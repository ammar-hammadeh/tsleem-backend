<?php

namespace Modules\Core\Http\Controllers;

use Exception;
use App\Models\City;
use App\Models\Type;
use App\Models\Company;
use App\Models\Category;
use App\Helper\LogHelper;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\UserAttachement;
use Modules\Core\Entities\User;
use Modules\Core\Mail\SendEmail;
use App\Helper\fileManagerHelper;
use App\Models\CompanyAttachement;
use App\Models\User as ModelsUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Modules\Core\Mail\NewAccountMail;
use App\Models\EngineerOffceCategories;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Mail\ResetPasswordRequestMail;
use App\Http\Controllers\NotificationController;

class AuthController extends Controller
{
    public function register_data()
    {
        $types = Type::whereIn('code', ['service_provider', 'design_office', 'contractor'])->get();
        $cities = City::all();
        $category = Category::all();
        return response()->json(["types" => $types, "cities" => $cities, 'category' => $category], 200);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "يوجد أخطاء الرجاء التأكد", "errors" => $validator->errors()], 500);
        }

        $credentials = $request->only('email', 'password');
        if ($token = $this->guard()->attempt($credentials)) {
            // log section
            $user_id =  $this->guard()->user()->id;
            $old_value = null;
            $new_value = null;
            $module = 'users';
            $method_id = 7;
            $message = __('logTr.Login');

            LogHelper::storeLog(
                $user_id,
                json_decode(json_encode($old_value)),
                json_decode(json_encode($new_value)),
                $module,
                $method_id,
                $message,
            );
            return $this->respondWithToken($token);
        }


        return response()->json(['message' => 'البريد الالكتروني أو كلمة المرور غير صحيحة'], 422);
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'city_id' => 'nullable|integer',
            // 'type_id' => 'nullable',
            'hardcopyid' => 'nullable|string',
            'phone' => 'nullable|string',
            // 'category_id' => 'nullable|integer',

        ], [
            'email.unique' => 'البريد الالكتروني موجود مسبقاً'
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        DB::beginTransaction();
        try {
            $data = array();
            $avatar = null;
            $signature = null;
            if ($request->hasFile('avatar')) {
                $avatar = 'users/' . fileManagerHelper::storefile('', $request->avatar, 'users');
                $data['avatar'] = $avatar;
            }
            if ($request->hasFile('signature')) {
                $signature = 'signatures/' . fileManagerHelper::storefile('', $request->signature, 'signatures');
                $data['signature'] = $signature;
            }
            $data['password'] = bcrypt($request->password);
            $data['type_id'] = Type::where('code', $request->type_id)->value('id');
            // if ($request->type_id == 'service_provider')
            $data['name'] = $request->owner_name;

            if (Auth::check()) {
                $user_type = Auth::user()->type_id;
                if (Type::where('id', $user_type)->value('code') == 'admin');
                $data['status'] = 'active';
            }
            $user = User::create(array_merge(
                $validator->validated(),
                $data
            ));
            if ($request->category_id) {
                $cat = Category::find($request->category_id);
                $user->Category()->sync($cat);
            }


            if (Type::where('id', $request->type_id)->value('code') != 'admin') {
                $result = $this->createCompany($request, $user);
                if ($result['status'] == 'false') {
                    DB::rollback();
                    return  response()->json(["errors" => $result['message']], $result['code']);
                } else {
                    $company = $result['data'];
                }
            }
            DB::commit();

            if ($user) {
                $user->update([
                    'company_id' => $company->id
                ]);
                $data = array(
                    "name" => $user->name,
                    "subject" => "Get Started, Welcome in " . env('APP_NAME')
                );
                if ($request->hasFile('ownerid_file')) {
                    $ownerid_file = fileManagerHelper::storefile($user->id, $request->ownerid_file, 'users');
                    UserAttachement::create([
                        'name' =>  'صورة هوية المالك',
                        'user_id' =>  $user->id,
                        'type' =>  '0',
                        'path' => $ownerid_file,
                        'expire' => $request->ownerid_expire,
                    ]);
                }

                // log section
                $user_id = $user->id;
                $old_value = null;
                $new_value = array_merge([
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->type->name,
                    'phone' => $user->phone,
                    'city' => $user->city_id ? $user->city->name : null,
                    'category_id' => $user->category_id ? $user->category->name : null,
                ], $result['newValue']);

                $module = 'users';
                $method_id = 6;
                $message = __('logTr.RegisterDone');

                LogHelper::storeLog(
                    $user_id,
                    json_decode(json_encode($old_value)),
                    json_decode(json_encode($new_value)),
                    $module,
                    $method_id,
                    $message,
                );

                dispatch(new SendEmailJob($user->email, new SendEmail($data, "NewAccount")))->onConnection('database');
                $notificationMessage = __('general.userNeedApprove');
                $link = "/users/view/" . $user->id;
                (new NotificationController)->addNotification(1, $notificationMessage, $link);
            }

            if (Auth::guard('api')->check())
                $message = "تم إنشاء الحساب بنجاح";
            else
                $message = "الرجاء تسجيل الدخول ورفع باقي المستندات لتفعيل الحساب";

            return response()->json(["message" => $message, "user" => $user], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e->getMessage()], 500);
        }
    }


    public function CreateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'city_id' => 'nullable|integer',
            // 'category_id' => 'nullable|integer',
            'hardcopyid' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        DB::beginTransaction();
        try {
            $data = array();
            $avatar = null;
            $signature = null;
            if ($request->hasFile('avatar')) {
                $avatar = 'users/' . fileManagerHelper::storefile('', $request->avatar, 'users');
                $data['avatar'] = $avatar;
            }
            if ($request->hasFile('signature')) {
                $signature = 'signatures/' . fileManagerHelper::storefile('', $request->signature, 'signatures');
                $data['signature'] = $signature;
            }
            $data['password'] = bcrypt($request->password);
            $data['type_id'] = Type::where('code', $request->type_id)->value('id');
            if (Auth::check()) {
                $user_type = Auth::user()->type_id;
                if (Type::where('id', $user_type)->value('code') == 'admin' || Type::where('id', $user_type)->value('code') == 'raft_company') {
                    $data['status'] = 'active';
                }
                if ($request->type_id != 'admin' && Type::where('id', $user_type)->value('code') != 'admin') {
                    $data['parent_id'] = Auth::user()->id;
                }
                if ($request->type_id == 'raft_company' && Type::where('id', $user_type)->value('code') == 'admin') {
                    $data['name'] = $request->company_name;
                }

                if (Type::where('id', $user_type)->value('code') == 'admin' && $request->type_id == 'raft_office') {
                    $raft_company = Company::find($request->raft_company_id);
                    $owner = $raft_company->owner_id;
                    $data['parent_id'] = $owner;
                }

                if ($request->type_id != 'admin') {
                    $data['type_id']  = Type::where('code', $request->type_id)->value('id');
                }
            }
            // if ($request->type_id == 'service_provider')
            if ($request->type_id != 'raft_company')
                $data['name'] = $request->owner_name;

            $user = User::create(array_merge(
                $validator->validated(),
                $data
            ));
            if ($request->category_id) {
                $cat = Category::find($request->category_id);
                $user->Category()->sync($cat);
            }

            if (Auth::check()) {
                $user_type = Auth::user()->type_id;
                if (Type::where('id', $user_type)->value('code') == 'admin') {
                    if ($user && $request->roles) {
                        $user->syncRoles($request->roles);
                    }
                }
            }
            if (Type::where('id', $request->type_id)->value('code') != 'admin') {
                $result = $this->createCompany($request, $user);
                if ($result['status'] == 'false') {
                    DB::rollback();
                    return  response()->json(["errors" => $result['message']], $result['code']);
                } else {
                    $company = $result['data'];
                }
            }
            DB::commit();

            if ($user) {
                $user->update([
                    'company_id' => $company->id
                ]);
                $Emaildata = array(
                    "name" => $user->name,
                    "subject" => "Get Started, Welcome in " . env('APP_NAME')
                );
                if ($request->hasFile('ownerid_file')) {
                    $ownerid_file = fileManagerHelper::storefile('ids', $request->ownerid_file, 'users');
                    UserAttachement::create([
                        'name' =>  'صورة هوية المالك',
                        'user_id' =>  $user->id,
                        'type' =>  '0',
                        'path' => $ownerid_file,
                        'expire' => $request->ownerid_expire,
                    ]);
                }
                dispatch(new SendEmailJob($user->email, new SendEmail($Emaildata, "NewAccount")))->onConnection('database');
            }

            if (Auth::guard('api')->check())
                $message = "تم إنشاء الحساب بنجاح";
            else
                $message = "نود إعلامك بأن الطلب الذي قمت بتقديمه تحت الدراسة";
            return response()->json(["message" => $message, "user" => $user], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e->getMessage()], 500);
        }
    }


    public function createCompany(Request $request, $user)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor|string',
            'commercial' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor|string',
            // 'commercial_expiration' => 'required_if:type_id,consulting_office|date',
            'owner_name' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor',
            'license' => 'required_if:type_id,service_provider',
            'type_id' => 'required',
        ]);

        if ($validator->fails()) {
            return array('status' => 'false', 'message' => $validator->errors(), 'data' => null, 'code' => 422);
        }

        $type = Type::where('code', $request->type_id)->value('id');
        $parent_id = null;
        $prefix = null;
        $kroky = null;
        if (Auth::check()) {
            $auth_user_type = Type::find(Auth::user()->type_id);
            if ($auth_user_type->code == 'raft_company' && $request->type_id == 'raft_office') {
                $company = Company::where('owner_id', Auth::user()->id)->first();
                $parent_id  = $company->id;
                $prefix  = $company->prefix;
                $kroky = $company->kroky;
            }
            if ($auth_user_type->code == 'admin' && $request->type_id == 'raft_office') {
                $raft_company = Company::find($request->raft_company_id);
                $prefix = $raft_company->prefix;
                $parent_id  = $raft_company->id;
                $kroky = $raft_company->kroky;
            }
        }
        $company = Company::create([
            'name' =>  $request->company_name,
            'commercial' => $request->commercial,
            'license' => $prefix != null ? $prefix . '-' . $request->license : $request->license,
            // 'commercial_expiration' => $request->commercial_expiration,
            'owner_id' => $user->id,
            'type_id' => $type,
            'owner_name' => $request->owner_name,
            'kroky' => $kroky == null ? $request->kroky : $kroky,
            'owner_hardcopyid' => $request->owner_hardcopyid,
            'parent_id' => $parent_id,
            'prefix' => $prefix == null ? $request->prefix : $prefix
        ]);

        $newValue = [
            'name' => $request->name,
            'commercial' => $request->commercial,
            'license' => $prefix != null ? $prefix . '-' . $request->license : $request->license,
            'owner name' => $request->owner_name,
            'kroky' => $kroky == null ? $request->kroky : $kroky,
            'Owner identification number' => $request->owner_hardcopyid,
            'prefix' => $prefix == null ? $request->prefix : $prefix,
        ];
        if (!$company)
            return array('status' => 'false', 'message' => 'no company found', 'data' => null, 'code' => 401);

        $data['company_id'] = $company->id;
        $data['type'] = '0';
        if ($request->hasFile('commercial_file')) {
            $commercial_file = fileManagerHelper::storefile($company->id, $request->commercial_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'السجل التجاري',
                'path' => $commercial_file,
                'expire' => $request->commercial_expiration,
            ]));
        }
        if ($request->hasFile('classification_file')) {
            $classification_file = fileManagerHelper::storefile($company->id, $request->classification_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة تصنيف بلدي',
                'path' => $classification_file,
                'expire' => $request->classification_expire,
            ]));
        }
        if ($request->hasFile('national_file')) {
            $national_file = fileManagerHelper::storefile($company->id, $request->national_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'العنوان الوطني',
                'path' => $national_file,
                'expire' => null,
            ]));
        }
        if ($request->hasFile('practice_file')) {
            $practice_file = fileManagerHelper::storefile($company->id, $request->practice_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة مزاولة الخدمة',
                'path' => $practice_file,
                'expire' => $request->practice_expire,
            ]));
        }
        if ($request->hasFile('business_file')) {
            $business_file = fileManagerHelper::storefile($company->id, $request->business_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'رخصة نشاط تجاري',
                'path' => $business_file,
                'expire' => $request->business_expire,
            ]));
        }

        if ($request->hasFile('social_security')) {
            $social_security = fileManagerHelper::storefile($company->id, $request->social_security, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'رخصة التأمينات الإجتماعية',
                'path' => $social_security,
                'expire' => $request->social_expire,
            ]));
        }
        if ($request->hasFile('zakat_income')) {
            $zakat_income = fileManagerHelper::storefile($company->id, $request->zakat_income, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'رخصة الزكاة والدخل',
                'path' => $zakat_income,
                'expire' => $request->zakat_expire,
            ]));
        }
        if ($request->hasFile('saudization')) {
            $saudization = fileManagerHelper::storefile($company->id, $request->saudization, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة السعودة',
                'path' => $saudization,
                'expire' => $request->saudization_expire,
            ]));
        }
        if ($request->hasFile('chamber_commerce')) {
            $chamber_commerce = fileManagerHelper::storefile($company->id, $request->chamber_commerce, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة الغرفة التجارية',
                'path' => $chamber_commerce,
                'expire' => $request->chamber_expire,
            ]));
        }
        if ($request->hasFile('tax_registration')) {
            $tax_registration = fileManagerHelper::storefile($company->id, $request->tax_registration, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة تسجيل الضريبة',
                'path' => $tax_registration,
                'expire' => $request->tax_expire,
            ]));
        }
        if ($request->hasFile('wage_protection')) {
            $wage_protection = fileManagerHelper::storefile($company->id, $request->wage_protection, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة حماية الأجور',
                'path' => $wage_protection,
                'expire' => $request->wage_expire,
            ]));
        }
        if ($request->hasFile('memorandum_association')) {
            $memorandum_association = fileManagerHelper::storefile($company->id, $request->memorandum_association, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'عقد التأسيس',
                'path' => $memorandum_association,
                'expire' => $request->memorandum_expire,
            ]));
        }
        if ($request->hasFile('seasonal_license')) {
            $seasonal_license = fileManagerHelper::storefile($company->id, $request->seasonal_license, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'الرخصة الموسمية',
                'path' => $seasonal_license,
                'expire' => null,
            ]));
        }


        if ($request->hasFile('assign_file')) {
            $assign_file = fileManagerHelper::storefile($company->id, $request->assign_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'محضر التخصيص',
                'path' => $assign_file,
                'expire' => null,
            ]));
        }

        if ($request->hasFile('delegateid')) {
            $delegateid = fileManagerHelper::storefile($company->id, $request->delegateid, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'هوية المفوض',
                'path' => $delegateid,
                'expire' => $request->delegateid_expire,
            ]));
        }

        if ($request->hasFile('delegation')) {
            $delegation = fileManagerHelper::storefile($company->id, $request->delegation, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'التفويض',
                'path' => $delegation,
                'expire' => $request->delegation_expire,
            ]));
        }
        if ($request->hasFile('hajj_license')) {
            $hajj_license = fileManagerHelper::storefile($company->id, $request->hajj_license, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'ترخيص خدمة الحج',
                'path' => $hajj_license,
                'expire' => $request->hajj_license_expire,
            ]));
        }

        return array('status' => 'true', 'message' => 'get data', 'data' => $company, 'newValue' => $newValue, 'code' => 200);
    }

    public function permission_me()
    {
        return response()->json([
            'permissions' => $this->userPermissions()
        ]);
    }

    public function me()
    {
        return response()->json([
            'user' => User::with('City', 'Companies', 'Category')->find($this->guard()->user()->id),
            'company' => Company::find($this->guard()->user()->company_id),
            'company_file' => CompanyAttachement::whereCompanyId($this->guard()->user()->company_id)->get(),
            'user_file' => UserAttachement::whereUserId($this->guard()->user()->id)->get(),
            'cities' => City::all()
        ]);
    }


    public function logout(Request $request)
    {
        try {
            $this->guard()->logout();
            return response()->json(['status' => 's uccess', 'message' => 'Logged out successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Session has been expired, please login in again.'], 500);
        }
    }

    public function refresh()
    {
        try {
            return $this->respondWithToken($this->guard('api')->refresh());
        } catch (Exception $e) {
            return response()->json(['message' => 'Session has been expired, please login in again.'], 401);
        }
    }

    public function passwordResetRequest(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|exists:users'
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => 'Check your inputs', 'data' => $validator->errors()], 422);
        }

        $token = $this->generateTokenForResetPassword($request['email']);

        $user = User::where('email', '=', $request['email'])->first();
        $data = array(
            "subject" => "Password reset request",
            "name" => $user->name,
            "email" => $user->email,
            "token" => $token
        );

        dispatch(new SendEmailJob($user->email, new SendEmail($data, "ResetPasswordRequest")))->onConnection('database');
        return response()->json([
            'url' => env('FRONT_URL') . '/reset?token=' . $token . '&email=' . $user->email,
            'message' => 'Please check your inbox, we have sent a link to reset password.', 'token' => $token
        ], 200);
    }

    public function generateTokenForResetPassword($email)
    {
        $isOtherToken = DB::table('password_resets')->where('email', $email)->orderBy('created_at', 'DESC')->first();

        if ($isOtherToken) {
            // Check the time difference.
            $totalMinute = Carbon::now()->diffInMinutes($isOtherToken->created_at);
            if ($totalMinute <= 15)
                return $isOtherToken->token;
        }

        $token = Str::random(80);;
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
        return $token;
    }


    public function reset(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|exists:users',
            'password' => 'required|string|confirmed|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['data' => $validator->errors()], 422);
        }
        // Verify if token is valid
        $token = DB::table('password_resets')->where([
            'email' => $request['email'],
            'token' => $request['token']
        ])->orderBy('created_at', 'DESC')
            ->first();

        if ($token) {
            // Check the time difference.
            $totalMinute = Carbon::now()->diffInMinutes($token->created_at);
            if ($totalMinute > 60) {
                return response()->json(['message' => 'Token is expired'], 500);
            }

            $user = User::whereEmail($request['email'])->first();
            // log section
            $user_id = $user->id;;
            $old_value = null;
            $new_value = null;
            $module = 'users';
            $method_id = 5;
            $message = __('logTr.ResetPassword');

            LogHelper::storeLog(
                $user_id,
                json_decode(json_encode($old_value)),
                json_decode(json_encode($new_value)),
                $module,
                $method_id,
                $message,
            );
        } else {
            return response()->json(['message' => 'Token is invalid'], 500);
        }
        DB::beginTransaction();
        try {
            $user = User::where('email', '=', $request['email'])->first();
            // return $request['password'];
            // update password
            // ->where('email', '=', $request['email'])
            if (!$user) {
                return response()->json(['message' => 'not found'], 404);
            }
            $user->update(['password' => bcrypt($request->password)]);
            // remove verification token data from db
            DB::table('password_resets')->where([
                'email' => $request['email'],
                'token' => $request['token']
            ])->delete();
            $data = array(
                "subject" => "Password Changed",
                "name" => $user->name,
                "email" => $user->email,
                "token" => $token
            );
            DB::commit();

            dispatch(new SendEmailJob($user->email, new SendEmail($data, "PasswordReset")))->onConnection('database');
            return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e->getMessage()], 500);
        }
    }

    protected function respondWithToken($token)
    {
        $user =  $this->guard()->user();
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
            'type' => Type::find($user->type_id),
            // 'permissions' => $this->userPermissions(),
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }

    public function guard()
    {
        return Auth::guard();
    }


    public function userPermissions()
    {
        $userPermissions = array();
        $permissions = Auth::user()->getAllPermissions();
        foreach ($permissions as $p) {
            array_push($userPermissions, $p->name);
        }
        return $userPermissions;
    }
}

<?php

namespace Modules\Core\Http\Controllers;

use Exception;
use App\Models\City;
use App\Models\Type;
use App\Models\Company;
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
use Illuminate\Support\Facades\Validator;
use Modules\Core\Mail\ResetPasswordRequestMail;

class AuthController extends Controller
{
    public function register_data()
    {
        $types = Type::whereIn('code', ['service_provider', 'design_office', 'contractor'])->get();
        $cities = City::all();
        return response()->json(["types" => $types, "cities" => $cities], 200);
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
            return $this->respondWithToken($token);
        }
        return response()->json(['message' => 'البريد الالكتروني أو كلمة المرور غير صحيحة'], 422);
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'city_id' => 'nullable|integer',
            // 'type_id' => 'nullable',
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
                if (Type::where('id', $user_type)->value('code') == 'admin');
                $data['status'] = 'active';
            }
            $user = User::create(array_merge(
                $validator->validated(),
                $data
            ));

            if (Type::where('id', $request->type_id)->value('code') != 'admin') {
                $company = $this->createCompany($request, $user);
                if (!$company) {
                    DB::rollback();
                    return  $company;
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
                    $ownerid_file = fileManagerHelper::storefile('ids', $request->ownerid_file, 'users');
                    UserAttachement::create([
                        'name' =>  'صورة هوية المالك',
                        'user_id' =>  $user->id,
                        'type' =>  '0',
                        'path' => $ownerid_file,
                        'expire' => $request->ownerid_expire,
                    ]);
                }
                dispatch(new SendEmailJob($user->email, new SendEmail($data, "NewAccount")))->onConnection('database');
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


    public function CreateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'city_id' => 'nullable|integer',
            // 'type_id' => 'nullable',
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
                if (Type::where('id', $user_type)->value('code') == 'admin') {
                    $data['status'] = 'active';
                }

                if ($request->type_id != 'admin') {
                    $data['parent_id'] = Auth::user()->id;
                    $data['type_id']  = Type::where('code', $request->type_id)->value('id');
                }
            }
            $user = User::create(array_merge(
                $validator->validated(),
                $data
            ));
            if (Auth::check()) {
                $user_type = Auth::user()->type_id;
                if (Type::where('id', $user_type)->value('code') == 'admin') {
                    if ($user && $request->roles) {
                        $user->syncRoles($request->roles);
                    }
                }
            }
            if (Type::where('id', $request->type_id)->value('code') != 'admin') {
                $company = $this->createCompany($request, $user);
                if (!$company) {
                    DB::rollback();
                    return  $company;
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
            'company_name' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor|string|between:2,100',
            'commercial' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor|string',
            'commercial_expiration' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor|date',
            'owner_name' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor',
            'license' => 'required_if:type_id,service_provider,raft_company',
            'type_id' => 'required'
        ]);

        if ($validator->fails()) {
            return false;
        }

        $type = Type::where('code', $request->type_id)->value('id');
        $company = Company::create([
            'name' =>  $request->company_name,
            'commercial' => $request->commercial,
            'license' => $request->license,
            'commercial_expiration' => $request->commercial_expiration,
            'owner_id' => $user->id,
            'type_id' => $type,
            'owner_name' => $request->owner_name,
            'owner_hardcopyid' => $request->owner_hardcopyid,

        ]);
        if (!$company)
            return false;

        $data['company_id'] = $company->id;
        $data['type'] = '0';
        if ($request->hasFile('commercial_file')) {
            $commercial_file = fileManagerHelper::storefile($company->id, $request->commercial_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'السجل التجاري',
                'path' => $commercial_file,
                'expire' => $request->commercial_expire,
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
        return $company;
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
            'user' => $this->guard()->user(),
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
            'url' => env('APP_FRONT_URL') . '/reset?token=' . $token . '&email=' . $user->email,
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
        } else {
            return response()->json(['message' => 'Token is invalid'], 500);
        }
        DB::beginTransaction();
        try {
            return $user = User::where('email', '=', $request['email'])->first();
            // return $request['password'];
            // update password
            // ->where('email', '=', $request['email'])
            if (!$user) {
                return response()->json(['message' => 'not found'], 404);
            }
            $user->update(['name' => 'jo']);
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

            // dispatch(new SendEmailJob($user->email, new SendEmail($data, "PasswordReset")))->onConnection('database');
            return response()->json(['message' => 'Password reset successfully'], 200);
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

<?php

namespace Modules\Core\Http\Controllers;

use App\Models\City;
use App\Models\Type;
use App\Models\Company;
use App\Models\Category;
use App\Jobs\SendEmailJob;
use App\Models\AssignCamp;
use Illuminate\Http\Request;
use App\Mail\EnableAccountMail;
use App\Models\UserAttachement;
use Modules\Core\Entities\User;
use App\Mail\DisableAccountMail;
use Modules\Core\Mail\SendEmail;
use App\Helper\fileManagerHelper;
use App\Models\CompanyAttachement;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\EngineerOffceCategories;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Rules\MatchOldPassword;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    public function filters()
    {
        $filters = [
            [
                'name' => 'start',
                'value' => '',
                'label' => __('general.Start'),
                'type' => 'date',
                'items' => ''
            ],
            [
                'name' => 'end',
                'value' => '',
                'label' => __('general.End'),
                'type' => 'date',
                'items' => ''
            ],
            [
                'name' => 'name',
                'value' => '',
                'label' => __('general.Name'),
                'type' => 'text',
                'items' => ''
            ],
            [
                'name' => 'company_name',
                'value' => '',
                'label' => __('general.company_name'),
                'type' => 'text',
                'items' => ''
            ],


        ];
        return $filters;
    }
    public function index(Request $request)
    {
        $paginate = env('PAGINATE');
        if ($request->has('paginate')) {
            $paginate = $request->paginate;
        }
        $office_type = Type::where('code', 'raft_office')->value('id');
        if (Auth::check()) {
            $user_type = Auth::user()->type_id;
            if (Type::where('id', $user_type)->value('code') == 'raft_company') {
                $filters = $this->filters();
                array_push($filters, [
                    'name' => 'type_id',
                    'value' => '',
                    'label' => __('general.user type'),
                    'type' => 'auto-complete',
                    'items' => Type::whereIn('code', ['raft_company', 'raft_office'])->get(),
                    'itemText' => 'name',
                    'itemValue' => 'id'
                ]);
                $users = User::where('parent_id', Auth::user()->id)->with('type', 'Company', 'roles');
                if ($request->name) {
                    $users->where('users.name', 'like', '%' . $request->name . '%');
                }
                if ($request->company_name) {
                    $users->whereHas('Company', function ($query) use ($request) {
                        $query->where('name', $request->company_name);
                    });
                }
                if ($request->type_id)
                    $users->where('users.type_id', $request->type_id);

                if ($request->start)
                    $users->whereDate('created_at', '>=', $request->start);
                if ($request->end)
                    $users->whereDate('created_at', '<=', $request->end);

                $users = $users->paginate($paginate);
                return response()->json(["message" => "Users get successfully", "data" => $users, 'filters' => $filters], 200);
            }
        }
        $filters = $this->filters();
        array_push($filters, [
            'name' => 'type_id',
            'value' => '',
            'label' => __('general.user type'),
            'type' => 'auto-complete',
            'items' => Type::get(),
            'itemText' => 'name',
            'itemValue' => 'id'
        ]);
        array_push($filters, [
            'name' => 'status',
            'value' => '',
            'label' => __('general.status'),
            'type' => 'auto-complete',
            'items' => [
                ['name' => 'pending', 'label' => __('general.pending')],
                ['name' => 'active', 'label' => __('general.active')],
                ['name' => 'disabled', 'label' => __('general.disabled')],
                ['name' => 'review', 'label' => __('general.review')],
                ['name' => 'rejected', 'label' => __('general.rejected')],
            ],
            'itemText' => 'label',
            'itemValue' => 'name'
        ]);

        $users = User::whereRaw('((parent_id is null) or (parent_id is not null and type_id =' . $office_type . '))')
            ->with('type', 'Company', 'roles');
        if ($request->name) {
            $users->where('users.name', 'like', '%' . $request->name . '%');
        }
        if ($request->company_name) {
            $users->whereHas('Company', function ($query) use ($request) {
                $query->where('name', $request->company_name);
            });
        }
        if ($request->type_id)
            $users->where('users.type_id', $request->type_id);

        if ($request->status)
            $users->where('users.status', $request->status);

        if ($request->start)
            $users->whereDate('created_at', '>=', $request->start);
        if ($request->end)
            $users->whereDate('created_at', '<=', $request->end);

        $users = $users
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($paginate);
        return response()->json(["message" => "Users get successfully", "data" => $users, 'filters' => $filters], 200);
    }

    public function view($id)
    {
        $user = User::with('Company.Attachement', 'roles', 'Attachement', 'type', 'city', 'category')->find($id);
        return response()->json(["message" => "User get successfully", "data" => $user], 200);
    }

    public function create_data()
    {
        $types = Type::withoutDisabled()->get();
        $cities = City::all();
        // $categories = Category::all();
        $roles = Role::get();
        // $engineer_office = EngineerOffceCategories::all();
        return response()->json(["roles" => $roles, "types" => $types, "cities" => $cities], 200);
    }

    public function CreateEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'city_id' => 'nullable|integer',
            'type_id' => 'nullable|integer',
            'hardcopyid' => 'nullable|string',
            'phone' => 'nullable|string',

        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        DB::beginTransaction();
        try {
            $parent_id = null;
            $type_id = '';
            $company_id = '';
            $user_type = Auth::user()->type_id;
            if ($request->type_id != 'admin' && Auth::guard('api')->user()->hasPermissionTo('add-employee') && Type::where('id', $user_type)->value('code') != 'admin') {
                $parent_id = Auth::user()->id;
                $type_id = Auth::user()->type_id;
                $company_id = Company::where('owner_id', Auth::user()->id)->value('id');
            } else {
                return response()->json(['message', 'لايمكن إضافة موظف، لاتملك الصلاحيات'], 500);
            }
            $data = array(
                'password' => bcrypt($request->password),
                'parent_id' => $parent_id,
                'type_id' => $type_id,
                'status' => 'active',
                'company_id' => $company_id,
                'employee' => 1
            );
            if ($request->hasFile('avatar')) {
                $data['avatar'] = 'users/' . fileManagerHelper::storefile('', $request->avatar, 'users');
            }
            if ($request->hasFile('signature')) {
                $data['signature'] = 'signatures/' . fileManagerHelper::storefile('', $request->signature, 'signatures');
            }

            if (Auth::check()) {
                if (Auth::user()->parent_id != null)
                    return response()->json(['message' => 'لايمكنك اضافة موظف'], 500);
            }
            $user = User::create(array_merge(
                $validator->validated(),
                $data
            ));

            if (Auth::check()) {
                $parent = Auth::user();
                $role = $parent->getRoleNames();
                if ($user) {
                    $user->syncRoles($role);
                }
            }
            DB::commit();

            if ($user) {
                $data = array(
                    "name" => $user->name,
                    "subject" => "Get Started, Welcome in " . env('APP_NAME')
                );
                if ($request->hasFile('ownerid_file')) {
                    $ownerid_file = fileManagerHelper::storefile('ids', $request->signature, 'users');
                    UserAttachement::create([
                        'name' =>  'العنوان الوطني',
                        'user_id' =>  $user->id,
                        'type' =>  '0',
                        'path' => $ownerid_file,
                        'expire' => $request->ownerid_expire,
                    ]);
                }
                dispatch(new SendEmailJob($user->email, new SendEmail($data, "NewAccount")))->onConnection('database');
            }
            return response()->json(["message" => "success register", "user" => $user], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e->getMessage()], 500);
        }
    }

    public function ShowEmployees(Request $request)
    {
        $paginate = env('PAGINATE');
        if ($request->has('paginate')) {
            $paginate = $request->paginate;
        }
        $user = Auth::user();
        $employee = User::where('parent_id', $user->id)->where('employee', 1)->paginate($paginate);
        return response()->json(['data' => $employee], 200);
    }

    public function viewUser()
    {
        $user = User::with('Type', 'Attachement', 'City', 'Category')->find(Auth::id());
        if ($user != null) {

            if ($user->status == 'rejected' || $user->status == 'pending') {

                $company = Company::with('Type', 'Attachement')->where('owner_id', $user->id)->first();
                $types = Type::whereIn('code', ['service_provider', 'design_office', 'contractor'])->get();
                $cities = City::get();
                if ($company != null) {
                    return response()->json(['user' => $user, 'company' => $company, 'types' => $types, 'cities' => $cities], 200);
                }
                // return response()->json(['user' => $user], 200);
            }
            return response()->json(['message' => 'لا يوجد صلاحية للدخول لهذه الصفحة'], 403);
        }
        return response()->json(['message' => 'المستخدم غير موجود'], 404);
    }

    public function updateRoles(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'roles' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        DB::beginTransaction();
        try {
            $user = User::find($id);
            if ($user) {
                $user->syncRoles($request->roles);
            }
            DB::commit();

            return response()->json(["message" => "Roles updated", "user" => $user], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e], 500);
        }
    }

    public function switchUserStatus($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);
            if ($user) {
                $data = array('status' => $request->status);
                if ($request->status == "active")
                    $notificationMessage = __('general.activeRequest');
                // elseif ($request->status == "disabled")
                //     $notificationMessage = __('general.disabledRequest');

                if ($request->has('reject_reason')) {
                    $notificationMessage = __('general.rejectRequest');
                    $data['reject_reason'] = $request->reject_reason;
                } else
                    $data['reject_reason'] = null;


                try {
                    DB::beginTransaction();

                    if ($request->has('roles'))
                        $user->syncRoles($request->roles);

                    $user->update($data);
                    // $company = company::where('owner_id', $user->id)->first();
                    // if (!$company)
                    //     return response()->json(['message' => 'there\'s no any company assigned to this user', 'code' => '401']);
                    // AssignCamp::where('receiver_cr', $company->license)
                    //     ->update([
                    //         'receiver_cr' => null,
                    //         'receiver_company_id' => $company->id
                    //     ]);

                    (new NotificationController)->addNotification($user->id, $notificationMessage, '#');
                    DB::commit();
                    $Emaildata = array(
                        "name" => $user->name,
                        "status" => __('general.' . $user->status),
                        "reject_reason" => $user->reject_reason,
                        "subject" => "تغيير حالة حساب" . env('APP_NAME')
                    );
                    // new SendEmail($Emaildata, "ApproveAccount");
                    // Mail::to($user->email)->send(new SendEmail($Emaildata, "ApproveAccount"));
                    dispatch(new SendEmailJob($user->email, new SendEmail($Emaildata, "ApproveAccount")))->onConnection('database');
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['message' => 'somthing wrong, please try again', 'error' => $e->getMessage()], 500);
                }
            }
            DB::commit();
            return response()->json(["message" => "Status updated", "user" => $user], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Failed to change you password, please check errors.", "errors" => $validator->errors()], 500);
        }


        if (User::find(Auth::guard()->user()->id)->update(['password' => Hash::make($request->new_password)]))
            return response()->json(["message" => "Your password successfully updated."], 200);
        else
            return response()->json(["message" => "Failed to change you password, please check errors."], 500);
    }

    public function resetPasswordByAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Failed to change you password, please check errors.", "errors" => $validator->errors()], 500);
        }


        if (User::find(Auth::guard()->user()->id)->update(['password' => Hash::make($request->password)]))
            return response()->json(["message" => "Your password successfully updated."], 200);
        else
            return response()->json(["message" => "Failed to change you password, please check errors."], 500);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
            // 'password' => 'required|string|confirmed|min:6',
            'city_id' => 'nullable|integer',
            // 'type_id' => 'nullable',
            'hardcopyid' => 'nullable|string',
            'phone' => 'nullable|string',

        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 500);
        }
        // $avatar = "";
        $data = array();
        if ($request->hasFile('avatar')) {
            // $path = $request->avatar->getClientOriginalName();
            $avatar = fileManagerHelper::storefile('users', $request->avatar, '');
            // $avatar = $request->file('avatar')->storeAs('', time() . $path, 'users');
            $data['avatar'] = $avatar;
        }
        if ($request->hasFile('signature')) {
            $signature = 'signatures/' . fileManagerHelper::storefile('', $request->signature, 'signatures');
            $data['signature'] = $signature;
        }
        DB::beginTransaction();
        try {
            if ($request->password)
                $data['password'] = bcrypt($request->password);
            // $data['type_id'] = Type::where('code', $request->type_id)->value('id');
            $data['status'] = 'pending';
            $data['reject_reason'] = null;
            $data['name'] = $request->owner_name;

            $user->update(array_merge($validator->validated(), $data));
            if ($request->category_id) {
                $cat = Category::find($request->category_id);
                $user->Category()->sync($cat);
            }

            $company = Company::where('owner_id', $user->id)->first();

            if ($user->status == 'pending' || $user->status == 'rejected') {
                $user->update([
                    'status' => 'review'
                ]);
            }
            if ($company != null) {
                $co = $this->updateCompany($request, $user);
                if (!$co) {
                    DB::rollback();
                    return  $co;
                }
            }


            DB::commit();
            if ($request->hasFile('ownerid_file')) {
                $attach = UserAttachement::where('user_id', $user->id)->where('name', 'صورة هوية المالك')->first();
                // dd($attach);
                if ($attach != null) {
                    if (Storage::disk('users')->exists($company->id . '/' . $attach->path)) {
                        Storage::delete('public/users/' . $attach->path);
                    }
                    $attach->delete();
                }

                $ownerid_file = fileManagerHelper::storefile($user->id, $request->ownerid_file, 'users');
                UserAttachement::create([
                    'name' =>  'صورة هوية المالك',
                    'user_id' =>  $user->id,
                    'type' =>  '0',
                    'path' => $ownerid_file,
                    'expire' => $request->ownerid_expire,
                ]);
                $notificationMessage = 'تم تحديث معلومات المستخدم';
                $link = "/users/view/" . $user->id;

                (new NotificationController)->addNotification(1, $notificationMessage, $link);
            }
            return response()->json(["message" => "نود اعلامك أنه سيتم إعادة دراسة الطلب مرة أخرى", 'user' => $user], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e->getMessage()], 500);
        }
    }

    public function updateCompany(Request $request, $user)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor|string|between:2,100',
            'commercial' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor|string',
            'commercial_expiration' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor|date',
            'owner_name' => 'required_if:type_id,service_provider,consulting_office,design_office,contractor',
            'license' => 'required_if:type_id,service_provider,raft_company',
            // 'type_id' => 'required'
        ]);

        if ($validator->fails()) {
            return array('status' => 'false', 'message' => $validator->errors(), 'data' => null, 'code' => 422);
        }
        $type = Type::where('code', $request->type_id)->value('id');
        $company = Company::where('owner_id', $user->id)->first();
        $company->update([
            'name' =>  $request->company_name,
            'commercial' => $request->commercial,
            'license' => $request->license,
            'commercial_expiration' => $request->commercial_expiration,
            'owner_id' => $user->id,
            // 'type_id' => $type,
            'owner_name' => $request->owner_name,
            'owner_hardcopyid' => $request->owner_hardcopyid,

        ]);

        if (!$company)
            return false;

        $data['company_id'] = $company->id;
        $data['type'] = '0';
        if ($request->commercial_expiration) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'السجل التجاري')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->commercial_expiration,
                ]);
            }
        }
        if ($request->hasFile('commercial_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'السجل التجاري')->first();
            // dd($attach);
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            $commercial_file = fileManagerHelper::storefile($company->id, $request->commercial_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'السجل التجاري',
                'path' => $commercial_file,
                'expire' => $request->commercial_expiration,
            ]));
        }

        if ($request->classification_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة تصنيف بلدي')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->classification_expire,
                ]);
            }
        }

        if ($request->hasFile('classification_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة تصنيف بلدي')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            $classification_file = fileManagerHelper::storefile($company->id, $request->classification_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة تصنيف بلدي',
                'path' => $classification_file,
                'expire' => $request->classification_expire,
            ]));
        }

        // if ($request->commercial_expire) {
        //     $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'العنوان الوطني')->first();
        // if ($attach != null) {
        //     $attach->update([
        //         'expire' => $request->commercial_expire,
        //     ]);
        // }
        // }

        if ($request->hasFile('national_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'العنوان الوطني')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }


            $national_file = fileManagerHelper::storefile($company->id, $request->national_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'العنوان الوطني',
                'path' => $national_file,
                'expire' => null,
            ]));
        }

        if ($request->practice_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة مزاولة الخدمة')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->practice_expire,
                ]);
            }
        }

        if ($request->hasFile('practice_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة مزاولة الخدمة')->first();
            if ($attach != null) {

                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }


            $practice_file = fileManagerHelper::storefile($company->id, $request->practice_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة مزاولة الخدمة',
                'path' => $practice_file,
                'expire' => $request->practice_expire,
            ]));
        }

        if ($request->business_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'رخصة نشاط تجاري')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->business_expire,
                ]);
            }
        }

        if ($request->hasFile('business_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'رخصة نشاط تجاري')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }
            $business_file = fileManagerHelper::storefile($company->id, $request->business_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'رخصة نشاط تجاري',
                'path' => $business_file,
                'expire' => $request->business_expire,
            ]));
        }


        if ($request->social_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'رخصة التأمينات الإجتماعية')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->social_expire,
                ]);
            }
        }

        if ($request->hasFile('social_security')) {
            $social_security = fileManagerHelper::storefile($company->id, $request->social_security, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'رخصة التأمينات الإجتماعية')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'رخصة التأمينات الإجتماعية',
                'path' => $social_security,
                'expire' => $request->social_expire,
            ]));
        }


        if ($request->zakat_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'رخصة الزكاة والدخل')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->zakat_expire,
                ]);
            }
        }

        if ($request->hasFile('zakat_income')) {
            $zakat_income = fileManagerHelper::storefile($company->id, $request->zakat_income, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'رخصة الزكاة والدخل')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'رخصة الزكاة والدخل',
                'path' => $zakat_income,
                'expire' => $request->zakat_expire,
            ]));
        }

        if ($request->saudization_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة السعودة')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->saudization_expire,
                ]);
            }
        }

        if ($request->hasFile('saudization')) {
            $saudization = fileManagerHelper::storefile($company->id, $request->saudization, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة السعودة')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة السعودة',
                'path' => $saudization,
                'expire' => $request->saudization_expire,
            ]));
        }

        if ($request->chamber_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة الغرفة التجارية')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->chamber_expire,
                ]);
            }
        }

        if ($request->hasFile('chamber_commerce')) {
            $chamber_commerce = fileManagerHelper::storefile($company->id, $request->chamber_commerce, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة الغرفة التجارية')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة الغرفة التجارية',
                'path' => $chamber_commerce,
                'expire' => $request->chamber_expire,
            ]));
        }

        if ($request->tax_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة تسجيل الضريبة')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->tax_expire,
                ]);
            }
        }

        if ($request->hasFile('tax_registration')) {
            $tax_registration = fileManagerHelper::storefile($company->id, $request->tax_registration, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة تسجيل الضريبة')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة تسجيل الضريبة',
                'path' => $tax_registration,
                'expire' => $request->tax_expire,
            ]));
        }

        if ($request->wage_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة حماية الأجور')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->wage_expire,
                ]);
            }
        }

        if ($request->hasFile('wage_protection')) {
            $wage_protection = fileManagerHelper::storefile($company->id, $request->wage_protection, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة حماية الأجور')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة حماية الأجور',
                'path' => $wage_protection,
                'expire' => $request->wage_expire,
            ]));
        }

        if ($request->memorandum_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'عقد التأسيس')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->memorandum_expire,
                ]);
            }
        }

        if ($request->hasFile('memorandum_association')) {
            $memorandum_association = fileManagerHelper::storefile($company->id, $request->memorandum_association, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'عقد التأسيس')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'عقد التأسيس',
                'path' => $memorandum_association,
                'expire' => $request->memorandum_expire,
            ]));
        }

        if ($request->seasonal_license_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'الرخصة الموسمية')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->seasonal_license_expire,
                ]);
            }
        }

        if ($request->hasFile('seasonal_license')) {
            $seasonal_license = fileManagerHelper::storefile($company->id, $request->seasonal_license, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'الرخصة الموسمية')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'الرخصة الموسمية',
                'path' => $seasonal_license,
                'expire' => $request->seasonal_license_expire,
            ]));
        }

        if ($request->hasFile('assign_file')) {
            $assign_file = fileManagerHelper::storefile($company->id, $request->assign_file, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'محضر التخصيص')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'محضر التخصيص',
                'path' => $assign_file,
                'expire' => null,
            ]));
        }



        if ($request->delegateid_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'هوية المفوض')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->delegateid_expire,
                ]);
            }
        }

        if ($request->hasFile('delegateid')) {
            $delegateid = fileManagerHelper::storefile($company->id, $request->delegateid, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'هوية المفوض')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'هوية المفوض',
                'path' => $delegateid,
                'expire' => null,
            ]));
        }


        if ($request->delegation_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'التفويض')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->delegation_expire,
                ]);
            }
        }

        if ($request->hasFile('delegation')) {
            $delegation = fileManagerHelper::storefile($company->id, $request->delegation, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'التفويض')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'التفويض',
                'path' => $delegation,
                'expire' => null,
            ]));
        }


        if ($request->hajj_license_expire) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'ترخيص خدمة الحج')->first();
            if ($attach != null) {
                $attach->update([
                    'expire' => $request->hajj_license_expire,
                ]);
            }
        }

        if ($request->hasFile('hajj_license')) {
            $hajj_license = fileManagerHelper::storefile($company->id, $request->hajj_license, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'ترخيص خدمة الحج')->first();
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                }
                $attach->delete();
            }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'ترخيص خدمة الحج',
                'path' => $hajj_license,
                'expire' => $request->hajj_license_expire,
            ]));
        }

        return array('status' => 'true', 'message' => 'get data', 'data' => $company, 'code' => 200);
    }


    public function updateMe(Request $request)
    {
        $id = Auth::guard()->user()->id;
        if ($request->change_signature) {
            if ($request->signature) {
                if (Auth::guard()->user()->signature != null) {
                    if (env('DISK') == 's3') {
                        Storage::disk('s3')->delete('signatures/' . Auth::guard()->user()->signature);
                    } else
                    if (file_exists(public_path('storage/' . Auth::guard()->user()->signature))) {
                        unlink(public_path('storage/' . Auth::guard()->user()->signature));
                    }
                }
                $user = User::find($id);
                $image_parts = explode(';base64,', $request->signature);
                $image_type_aux = explode('image/', $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $fileName =  uniqid() . '.' . $image_type;
                // $file = storage_path('app\public\signatures\\') . $fileName;
                // Storage::put('public/signatures/' . $fileName, $image_base64);


                if (env('DISK') == 's3')
                    $name = Storage::disk(env('DISK'))->put(
                        'signatures/' . $fileName,
                        $image_base64,
                        'public'
                    );
                else
                    Storage::put('public/signatures/' . $fileName, $image_base64);

                // $a = file_put_contents($file, $image_base64);
                // return response()->json([$a]);

                $signature = 'signatures/' . $fileName;
                $data['signature'] =  $signature;
                $user->update($data);
                return response()->json(["message" => "signature updated successfully", 'user' => $user, 'signeture' => $signature], 200);
            }
        } else {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $id,
                'phone' => 'required|string',
                'city_id' => 'nullable',
                // 'category_id' => 'nullable',
                // 'avatar'=>'string',
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 500);
            }
            // $avatar = "";
            $data = array();
            if ($request->hasFile('avatar')) {
                $data['avatar'] = 'users/' . fileManagerHelper::storefile('', $request->avatar, 'users');
            }
            if ($request->hasFile('signature')) {
                $signature = 'signatures/' . fileManagerHelper::storefile('', $request->signature, 'signatures');
                $data['signature'] = $signature;
            }
            DB::beginTransaction();
            try {

                $user = User::find($id);
                $user->update(array_merge($validator->validated(), $data));
                if ($request->category_id) {
                    $cate = Category::find($request->category_id);
                    $user->Category()->sync($cate);
                }
                if (Auth::check()) {
                    $user_type = Auth::user()->type_id;
                    if (Type::where('id', $user_type)->value('code') == 'admin') {
                        if ($user && $request->roles) {
                            $user->syncRoles($request->roles);
                        }
                    }
                }


                DB::commit();
                return response()->json(["message" => "User updated successfully", 'user' => $user], 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(["message" => "Please check errors", "errors" => $e->getMessage()], 500);
            }
        }
    }

    public function deleteAvatar(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);
            $avatar = $user->avatar;
            if ($user->update(['avatar' => null])) {
                if (file_exists(public_path('storage/user/' . $avatar))) {
                    unlink(public_path('storage/user/' . $avatar));
                }
                return response()->json(['message' => 'Deleted'], 200);
            }
            DB::commit();
            return response()->json(["message" => "User updated successfully", 'user' => $user], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e], 500);
        }
    }


    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . Auth::guard('api')->user()->id,
            // 'phone' => 'required|string',
            // 'avatar'=>'string',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 500);
        }
        $avatar = "";
        if ($request->hasFile('avatar')) {
            // $path = $request->avatar->getClientOriginalName();
            $avatar = fileManagerHelper::storefile('users', $request->avatar, '');
            // $avatar = $request->file('avatar')->storeAs('', time() . $path, 'users');
        }
        DB::beginTransaction();
        try {

            $user = User::find(Auth::guard('api')->user()->id);
            $user->update($request->except('avatar') + ['avatar' => $avatar]);
            DB::commit();
            return response()->json(["message" => "User updated successfully"], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e], 500);
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user)
            return response()->json(['message' => "المستخدم غير موجود"], 402);

        try {
            DB::beginTransaction();

            $userCompany = Company::where('owner_id', $id)->first();
            Company::where('owner_id', $id)->delete();

            $userChildren = User::where('parent_id', $id)
                ->orWhere('id', $id)->get();
            User::where('parent_id', $id)->delete();
            User::where('id', $id)->delete();

            $companyAttachments = CompanyAttachement::where('company_id', $userCompany->id)->get();
            foreach ($companyAttachments as $attachment) {
                if (Storage::disk('company')->exists($attachment->company_id . '/' . $attachment->path)) {
                    Storage::delete('public/company/' . $attachment->path);
                    $attachment->delete();
                }
            }

            foreach ($userChildren as $attachment) {
                if (Storage::disk('users')->exists($attachment->user_id . '/' . $attachment->path)) {
                    Storage::delete('public/users/' . $attachment->path);
                    $attachment->delete();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => "يرجى المحاولة لاحقاً", 'error' => $e->getMessage()], 500);
        }
        return response()->json(['message' =>  'user has been deleted'], 200);
    }


    public function PendingUsers(Request $request)
    {
        $paginate = env('PAGINATE');
        if ($request->has('paginate')) {
            $paginate = $request->paginate;
        }
        $users = User::whereIn('status', ['pending', 'review'])->with('Type', 'Company')->paginate($paginate);
        return response()->json(['data' => $users], 200);
    }

    public function UpdateUserRole($id, Request $request)
    {
        $user = User::find($id);
        if ($user != null) {
            $user->syncRoles($request->roles);
            $user->roles = $user->roles;
            return response()->json(['message' => 'تم تعديل الصلاحيات بنجاح', 'data' => $user], 200);
        }
        return response()->json(['message' => 'لايوجد معلومات مطابقة'], 404);
    }
}

<?php

namespace Modules\Core\Http\Controllers;

use App\Models\City;
use App\Models\Type;
use App\Jobs\SendEmailJob;
use Illuminate\Http\Request;
use App\Mail\EnableAccountMail;
use App\Models\UserAttachement;
use Modules\Core\Entities\User;
use App\Mail\DisableAccountMail;
use Modules\Core\Mail\SendEmail;
use App\Helper\fileManagerHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\AssignCamp;
use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyAttachement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Rules\MatchOldPassword;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $paginate = env('PAGINATE');
        if ($request->has('paginate')) {
            $paginate = $request->paginate;
        }
        $users = User::whereNull('parent_id')->with('Company')->paginate($paginate);
        return response()->json(["message" => "Users get successfully", "data" => $users], 200);
    }

    public function view($id)
    {
        $user = User::with('roles')->find($id);
        return response()->json(["message" => "User get successfully", "data" => $user], 200);
    }

    public function create_data()
    {
        $types = Type::get();
        $cities = City::all();
        $categories = Category::all();
        $roles = Role::get();
        return response()->json(["roles" => $roles, "types" => $types, "cities" => $cities, 'categories' => $categories], 200);
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
            $parent_id = '';
            $type_id = '';
            $company_id = '';
            $user_type = Auth::user()->type_id;
            if ($request->type_id != 'admin' && Auth::guard('api')->user()->hasPermissionTo('add-employee') && Type::where('id', $user_type)->value('code') != 'admin') {
                $parent_id = Auth::user()->id;
                $type_id = Auth::user()->type_id;
                $company_id = Company::where('owner_id', Auth::user()->id)->value('id');
            }
            $data = array(
                'password' => bcrypt($request->password),
                'parent_id' => $parent_id,
                'type_id' => $type_id,
                'status' => 'active',
                'company_id' => $company_id
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
        $employee = User::where('parent_id', $user->id)->paginate($paginate);
        return response()->json(['data' => $employee], 200);
    }

    public function viewUser()
    {
        $user = User::with('Attachement')->find(Auth::id());
        if ($user != null) {

            if ($user->status == 'rejected') {

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
                if ($request->has('reject_reason'))
                    $data['reject_reason'] = $request->reject_reason;

                if ($request->has('roles'))
                    $user->syncRoles($request->roles);

                try {
                    DB::beginTransaction();
                    $user->update($data);
                    $company = company::where('owner_id', $user)->first();
                    if (!$company)
                        return response()->json(['message' => 'there\'s no any company assigned to this user']);
                    AssignCamp::where('receiver_cr', $company->license)
                        ->update([
                            'receiver_cr' => null,
                            'receiver_company_id' => $company->id
                        ]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['message' => 'somthing wrong, please try again']);
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
            'name' => 'required|string|between:2,100',
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
            $path = $request->avatar->getClientOriginalName();
            $avatar = $request->file('avatar')->storeAs('', time() . $path, 'users');
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
            $data['type_id'] = Type::where('code', $request->type_id)->value('id');
            $data['status'] = 'pending';
            $data['reject_reason'] = null;
            $user->update(array_merge($validator->validated(), $data));

            $company = Company::where('owner_id', $user->id)->first();

            if ($company != null) {
                $co = $this->updateCompany($request, $user);
                if (!$co) {
                    DB::rollback();
                    return  $co;
                }
            }



            DB::commit();
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
            'type_id' => 'required'
        ]);

        if ($validator->fails()) {
            return false;

            // return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $type = Type::where('code', $request->type_id)->value('id');
        $company = Company::where('owner_id', $user->id)->first();
        $company->update([
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
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'السجل التجاري')->first();
            // dd($attach);
            if ($attach != null) {
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                    $attach->delete();
                }
            }

            $commercial_file = fileManagerHelper::storefile($company->id, $request->commercial_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'السجل التجاري',
                'path' => $commercial_file,
                'expire' => $request->commercial_expire,
            ]));
        }
        if ($request->hasFile('classification_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة تصنيف بلدي')->first();
            if ($attach != null)
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                    $attach->delete();
                }

            $classification_file = fileManagerHelper::storefile($company->id, $request->classification_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة تصنيف بلدي',
                'path' => $classification_file,
                'expire' => $request->classification_expire,
            ]));
        }
        if ($request->hasFile('national_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'العنوان الوطني')->first();
            if ($attach != null)
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                    $attach->delete();
                }


            $national_file = fileManagerHelper::storefile($company->id, $request->national_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'العنوان الوطني',
                'path' => $national_file,
                'expire' => null,
            ]));
        }
        if ($request->hasFile('practice_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة مزاولة الخدمة')->first();
            if ($attach != null)
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                    $attach->delete();
                }


            $practice_file = fileManagerHelper::storefile($company->id, $request->practice_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة مزاولة الخدمة',
                'path' => $practice_file,
                'expire' => $request->practice_expire,
            ]));
        }
        if ($request->hasFile('business_file')) {
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'رخصة نشاط تجاري')->first();
            if ($attach != null)
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                    $attach->delete();
                }
            $business_file = fileManagerHelper::storefile($company->id, $request->business_file, 'company');
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'رخصة نشاط تجاري',
                'path' => $business_file,
                'expire' => $request->business_expire,
            ]));
        }

        if ($request->hasFile('social_security')) {
            $social_security = fileManagerHelper::storefile($company->id, $request->social_security, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'رخصة التأمينات الإجتماعية')->first();
            if ($attach != null)
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                    $attach->delete();
                }
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'رخصة التأمينات الإجتماعية',
                'path' => $social_security,
                'expire' => $request->social_expire,
            ]));
        }
        if ($request->hasFile('zakat_income')) {
            $zakat_income = fileManagerHelper::storefile($company->id, $request->zakat_income, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'رخصة الزكاة والدخل')->first();
            if ($attach != null)
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                    $attach->delete();
                }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'رخصة الزكاة والدخل',
                'path' => $zakat_income,
                'expire' => $request->zakat_expire,
            ]));
        }
        if ($request->hasFile('saudization')) {
            $saudization = fileManagerHelper::storefile($company->id, $request->saudization, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة السعودة')->first();
            if ($attach != null)
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                    $attach->delete();
                }
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة السعودة',
                'path' => $saudization,
                'expire' => $request->saudization_expire,
            ]));
        }
        if ($request->hasFile('chamber_commerce')) {
            $chamber_commerce = fileManagerHelper::storefile($company->id, $request->chamber_commerce, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة الغرفة التجارية')->first();
            if ($attach != null)
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                    $attach->delete();
                }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة الغرفة التجارية',
                'path' => $chamber_commerce,
                'expire' => $request->chamber_expire,
            ]));
        }
        if ($request->hasFile('tax_registration')) {
            $tax_registration = fileManagerHelper::storefile($company->id, $request->tax_registration, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة تسجيل الضريبة')->first();
            if ($attach != null)
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                    $attach->delete();
                }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة تسجيل الضريبة',
                'path' => $tax_registration,
                'expire' => $request->tax_expire,
            ]));
        }
        if ($request->hasFile('wage_protection')) {
            $wage_protection = fileManagerHelper::storefile($company->id, $request->wage_protection, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'شهادة حماية الأجور')->first();
            if ($attach != null)
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                    $attach->delete();
                }

            CompanyAttachement::create(array_merge($data, [
                'name' =>  'شهادة حماية الأجور',
                'path' => $wage_protection,
                'expire' => $request->wage_expire,
            ]));
        }
        if ($request->hasFile('memorandum_association')) {
            $memorandum_association = fileManagerHelper::storefile($company->id, $request->memorandum_association, 'company');
            $attach = CompanyAttachement::where('company_id', $company->id)->where('name', 'عقد التأسيس')->first();
            if ($attach != null)
                if (Storage::disk('company')->exists($company->id . '/' . $attach->path)) {
                    Storage::delete('public/company/' . $attach->path);
                    $attach->delete();
                }
            CompanyAttachement::create(array_merge($data, [
                'name' =>  'عقد التأسيس',
                'path' => $memorandum_association,
                'expire' => $request->memorandum_expire,
            ]));
        }

        return $company;
    }


    public function updateMe(Request $request)
    {
        $id = Auth::guard()->user()->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string',
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
            $path = $request->avatar->getClientOriginalName();
            $avatar = $request->file('avatar')->storeAs('', time() . $path, 'users');
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
        # code...
        $stage = User::find($id);
        if (!$stage) {
            return response()->json(['message' => "user not found"], 404);
        }
        $stage->delete();
        return response()->json(['message' =>  'user has been deleted'], 200);
    }


    public function PendingUsers(Request $request)
    {
        $paginate = env('PAGINATE');
        if ($request->has('paginate')) {
            $paginate = $request->paginate;
        }
        $users = User::where('status', 'pending')->with('Type', 'Company')->paginate($paginate);
        return response()->json(['data' => $users], 200);
    }
}

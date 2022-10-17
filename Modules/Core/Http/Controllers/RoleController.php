<?php

namespace Modules\Core\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Entities\Permission as EntitiesPermission;

class RoleController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        try {
            DB::beginTransaction();
            $role = Role::create([
                'name' => $request->name
            ]);
            if ($role) {
                $role->syncPermissions($request->permissions);
                DB::commit();
                return response()->json(["message" => "Role created successfully", "data" => $role], 200);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e], 500);
        }
    }

    public function get_permissions_users()
    {
        $permissions = EntitiesPermission::all();
        $users = User::whereNull('parent_id')->get();
        return response()->json(["permissions" => $permissions, "users" => $users], 200);
    }

    public function index()
    {
        $roles = Role::all()->toArray();
        return response()->json(["message" => "Roles get successfully", "data" => $roles], 200);
    }

    public function update(Request $request, $id)
    {

        $role = Role::find($id);
        //        if ($request->isMethod('post')) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name,' . $id
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 500);
        }

        if ($role->update(['name' => $request->name])) {
            $role->syncPermissions($request->permissions);
            return response()->json(["message" => "Role updated successfully", "data" => $role], 200);
        } else
            return response()->json(["message" => "Please Check errors"], 500);
        //        }
        //        else if ($request->isMethod('get')) {
        //            $permissions = Permission::getPermissions()->pluck('name')->toArray();
        //            $selected = $role->getPermissionNames()->toArray();
        //            return response()->json(["message" => "Role get successfully", "data" => ["role" => $role, "selected" => $selected, "permissions" => $permissions]], 200);
        //        }
    }

    public function view($id)
    {
        $role = Role::with(['permissions', 'users'])->find($id);
        //        $permissions = $role->getPermissionNames();
        if (empty($role)) {
            return response()->json(["message" => "No role with this id"], 404);
        }
        return response()->json(["message" => "Permission get successfully", "data" =>  $role], 200);
    }

    public function delete($id)
    {
        $role = Role::find($id);
        if (empty($role)) {
            return response()->json(["message" => "No role with this id"], 404);
        } elseif ($role->delete()) {
            return response()->json(['message' => 'Role deleted successfully'], 200);
        } else {
            return response()->json(["message" => "Please Check errors"], 500);
        }
    }
}

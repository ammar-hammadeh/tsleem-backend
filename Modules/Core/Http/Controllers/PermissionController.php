<?php

namespace Modules\Core\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
// use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Modules\Core\Entities\Permission;

class PermissionController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(["msg" => "Please Check errors", "errors" => $validator->errors()], 500);
        }
        try {
            DB::beginTransaction();
            $permission = Permission::findOrCreate($request->name);
            if ($permission) {
                $permission->syncRoles($request->roles);
                DB::commit();
                return response()->json(["message" => "Permission created successfully", "data" => $permission], 200);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e], 500);
        }

        if (Permission::findOrCreate($request->name))
            return response()->json(["msg" => "Permission created successfully"], 200);
    }

    public function index()
    {
        $permissions = Permission::all();
        return response()->json(["msg" => "Permission get successfully", "data" => $permissions], 200);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::find($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(["msg" => "Please Check errors", "errors" => $validator->errors()], 500);
        }

        if ($permission->update(['name' => $request->name])) {
            $permission->syncRoles($request->roles);
            return response()->json(["msg" => "Permission updated successfully"], 200);
        } else
            return response()->json(["msg" => "Please Check errors"], 500);
    }

    public function view($id)
    {
        $permission = Permission::getPermission(['id' => $id]);
        if (empty($permission)) {
            return response()->json(["msg" => "No permission with this id"], 404);
        }
        return response()->json(["msg" => "Permission get successfully", "data" => $permission], 200);
    }

    public function delete($id)
    {
        $permission = Permission::find($id);
        if (empty($permission)) {
            return response()->json(["msg" => "No permission with this id"], 404);
        } elseif ($permission->delete()) {
            return response()->json(['msg' => 'Permission deleted successfully'], 200);
        } else {
            return response()->json(["msg" => "Please Check errors"], 500);
        }
    }

    public function unAssignRole(Request $request, $id)
    {
        $permission = Permission::find($id);
        $validator = Validator::make($request->all(), [
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(["msg" => "Please Check errors", "errors" => $validator->errors()], 500);
        }

        if ($permission->removeRole($request->role)) {
            return response()->json(["msg" => "Permission updated successfully"], 200);
        } else
            return response()->json(["msg" => "Please Check errors"], 500);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\EngineerOffceCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EngineerOfficeCategoryController extends Controller
{
    public function index()
    {
        $engineer_offices = EngineerOffceCategories::all();
        return response()->json(["data" => $engineer_offices], 200);
    }

    public function edit($id)
    {
        $engineer_office = EngineerOffceCategories::find($id);
        if (!$engineer_office) {
            return response()->json(["message" => "لايوجد بيانات مطابقة"], 404);
        }
        return response()->json(["data" => $engineer_office], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        DB::beginTransaction();
        try {
            $res = EngineerOffceCategories::create($request->input());
            DB::commit();
            if ($res) {
                return response()->json(["message" => "تم إضافة فئة جديدة بنجاح", "engineer_office" => $res], 200);
            } else {
                return response()->json(["message" => "an error occurred"], 500);
            }
        } catch (\Exception $e) {
            return response()->json(["message" => "EngineerOffceCategories updated fail", "error" => $e], 500);
        }
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $type = EngineerOffceCategories::find($id);
        DB::beginTransaction();
        try {
            $type->update($request->input());
            DB::commit();
            return response()->json(["message" => "تم تحديث الفئة بنجاح"], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => "EngineerOffceCategories updated fail", "error" => $e], 500);
        }
    }

    public function destroy($id)
    {
        $engineer_office = EngineerOffceCategories::find($id);
        if ($engineer_office) {
            $engineer_office->delete();
            return response()->json(['message' => 'تم حذف الفئة بنجاح']);
        } else {
            return response()->json(['message' => 'لايوجد بيانات مطابقة']);
        }
    }

}

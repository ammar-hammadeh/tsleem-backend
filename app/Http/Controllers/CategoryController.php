<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json(["data" => $categories], 200);
    }

    public function edit($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(["message" => "لايوجد بيانات مطابقة"], 404);
        }
        return response()->json(["data" => $category], 200);
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
            $res = Category::create($request->input());
            DB::commit();
            if ($res) {
                return response()->json(["message" => "تم إضافة فئة جديدة بنجاح", "category" => $res], 200);
            } else {
                return response()->json(["message" => "an error occurred"], 500);
            }
        } catch (\Exception $e) {
            return response()->json(["message" => "Category updated fail", "error" => $e], 500);
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
        $type = Category::find($id);
        DB::beginTransaction();
        try {
            $type->update($request->input());
            DB::commit();
            return response()->json(["message" => "تم تحديث الفئة بنجاح"], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => "Category updated fail", "error" => $e], 500);
        }
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if ($category) {
            $category->delete();
            return response()->json(['message' => 'تم حذف الفئة بنجاح']);
        } else {
            return response()->json(['message' => 'لايوجد بيانات مطابقة']);
        }
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TypeController extends Controller
{
    public function index()
    {
        $types = Type::all();
        return response()->json(["data" => $types], 200);
    }

    public function edit($id)
    {
        $type = Type::find($id);
        if (!$type) {
            return response()->json(["message" => "not found"], 404);
        }
        return response()->json(["data" => $type], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'signer' => 'required|between:0,1',

        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        DB::beginTransaction();
        try {
            $res = Type::create($request->input());
            DB::commit();
            if ($res) {
                return response()->json(["message" => "add new type", "type" => $res], 200);
            } else {
                return response()->json(["message" => "an error occurred"], 500);
            }
        } catch (\Exception $e) {
            return response()->json(["message" => "types updated fail", "error" => $e], 500);
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
        $type = Type::find($id);
        if (!$type) {
            return response()->json(["message" => "not found"], 404);
        }
        DB::beginTransaction();
        try {
            $type->update($request->input());
            DB::commit();
            return response()->json(["message" => "types updated successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => "types updated fail", "error" => $e], 500);
        }
    }
}

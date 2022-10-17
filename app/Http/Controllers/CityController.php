<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::all();
        return response()->json(["data" => $cities], 200);
    }

    public function edit($id)
    {
        $city = City::find($id);
        if (!$city) {
            return response()->json(["message" => "not found"], 404);
        }
        return response()->json(["data" => $city], 200);
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
            $res = City::create($request->input());
            DB::commit();
            if ($res) {
                return response()->json(["message" => "City added successfully", "city" => $res], 200);
            } else {
                return response()->json(["message" => "an error occurred"], 500);
            }
        } catch (\Exception $e) {
            return response()->json(["message" => "City updated fail", "error" => $e], 500);
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
        $type = City::find($id);
        DB::beginTransaction();
        try {
            $type->update($request->input());
            DB::commit();
            return response()->json(["message" => "City updated successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => "City updated fail", "error" => $e], 500);
        }
    }
}

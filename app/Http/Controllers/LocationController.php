<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function index()
    {
        $est = Location::all();
        return response()->json(['data' => $est], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $est = Location::create($validator->validated());
        return response()->json(["message" => "Location added successfully", "data" => $est], 200);
    }
    public function edit($id)
    {
        $est = Location::find($id);
        if (!$est) {
            return response()->json(["message" => "Data not found"], 500);
        }
        return response()->json(["data" => $est], 200);
    }
    public function update(Request $request, $id)
    {
        $est = Location::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
            }
            $est->update($validator->validated());
            return response()->json(["message" => "Location updated successfully", "data" => $est], 200);
        }
    }
    public function destroy($id)
    {
        $est = Location::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $est->delete();
            return response()->json(["message" => "Location deleted successfully"], 200);
        }
    }
}

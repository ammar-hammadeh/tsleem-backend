<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index()
    {
        $est = Zone::all();
        return response()->json(['data' => $est], 200);
    }
    public function store()
    {
        $est = Zone::create();
        return response()->json(["message" => "Zone added successfully", "data" => $est], 200);
    }
    // public function update(Request $request, $id)
    // {
    //     $est = Zone::find($id);
    //     if ($est == null) {
    //         return response()->json(["message" => "Data not found"], 500);
    //     } else {
    //         $validator = Validator::make($request->all(), [
    //             'name' => 'nullable|string',
    //             'location_id' => 'required|integer',
    //         ]);
    //         if ($validator->fails()) {
    //             return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
    //         }
    //         $est = Zone::create($validator->validated());
    //         return response()->json(["message" => "Zone updated successfully", "data" => $est], 200);
    //     }
    // }
    public function destroy($id)
    {
        $est = Zone::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $est->delete();
            return response()->json(["message" => "Zone deleted successfully"], 200);
        }
    }
}

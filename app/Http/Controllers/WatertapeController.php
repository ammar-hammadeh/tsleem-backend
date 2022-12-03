<?php

namespace App\Http\Controllers;

use App\Models\Camp;
use App\Models\Location;
use App\Models\Watertape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WatertapeController extends Controller
{
    public function index()
    {
        $est = Watertape::with('Location', 'Camp')->get();
        return response()->json(['data' => $est], 200);
    }
    public function get_data()
    {
        $locations = Location::all();
        $camps = Camp::all();
        return response()->json(['locations' => $locations, 'camps' => $camps], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'location_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $est = Watertape::create($validator->validated());
        return response()->json(["message" => "Watertape added successfully", "data" => $est], 200);
    }

    public function storeWitCamps(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'location_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $est = Watertape::create($validator->validated());
        $camps = Camp::find($request->camps);
        $est->Camp()->sync($camps);
        return response()->json(["message" => "Washroom added successfully", "data" => $est], 200);
    }

    public function edit($id)
    {
        $est = Watertape::with('Camp')->find($id);
        if (!$est) {
            return response()->json(["message" => "Data not found"], 500);
        }
        $locations = Location::all();
        $camps = Camp::all();
        return response()->json(["data" => $est, 'locations' => $locations, 'camps' => $camps], 200);
    }

    public function update(Request $request, $id)
    {
        $est = Watertape::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'location_id' => 'nullable|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
            }
            $est->update($validator->validated());
            return response()->json(["message" => "Watertape updated successfully", "data" => $est], 200);
        }
    }

    public function updateWithCamps(Request $request, $id)
    {
        $est = Watertape::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'location_id' => 'nullable|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
            }
            $est->update($validator->validated());
            $camps = camp::find($request->camps);
            $est->Camp()->sync($camps);
            return response()->json(["message" => "Watertape updated successfully", "data" => $est], 200);
        }
    }
    public function destroy($id)
    {
        $est = Watertape::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $est->delete();
            return response()->json(["message" => "Watertape deleted successfully"], 200);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Camp;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\ElectricalMeter;
use Illuminate\Support\Facades\Validator;

class ElectricalMeterController extends Controller
{
    public function index()
    {
        $est = ElectricalMeter::with('Location', 'Camp')->get();
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
            'type' => 'nullable|string',
            'subscription_number' => 'nullable|string',
            'metric_capacity' => 'nullable|string',
            'closest_cabin' => 'nullable|string',
            'location_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $est = ElectricalMeter::create($validator->validated());
        return response()->json(["message" => "ElectricalMeter added successfully", "data" => $est], 200);
    }

    public function storeWitCamps(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'type' => 'nullable|string',
            'subscription_number' => 'nullable|string',
            'metric_capacity' => 'nullable|string',
            'closest_cabin' => 'nullable|string',
            'location_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $est = ElectricalMeter::create($validator->validated());
        if ($request->has('camps')) {
            $camps = Camp::find($request->camps);
            if (!$camps) {
                return response()->json(["message" => "camp not found"], 404);
            }
            $est->Camp()->sync($camps);
        }
        return response()->json(["message" => "ElectricalMeter added successfully", "data" => $est], 200);
    }

    public function edit($id)
    {
        $est = ElectricalMeter::with('Camp')->find($id);
        if (!$est) {
            return response()->json(["message" => "Data not found"], 500);
        }
        $camps = Camp::all();
        $locations = Location::all();
        return response()->json(["data" => $est, 'camps' => $camps, 'locations' => $locations], 200);
    }

    public function update(Request $request, $id)
    {
        $est = ElectricalMeter::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'type' => 'nullable|string',
                'subscription_number' => 'nullable|string',
                'metric_capacity' => 'nullable|string',
                'closest_cabin' => 'nullable|string',
                'location_id' => 'nullable|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
            }
            $est->update($validator->validated());
            return response()->json(["message" => "ElectricalMeter updated successfully", "data" => $est], 200);
        }
    }

    public function updateWithCamps(Request $request, $id)
    {
        $est = ElectricalMeter::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'type' => 'nullable|string',
                'subscription_number' => 'nullable|string',
                'metric_capacity' => 'nullable|string',
                'closest_cabin' => 'nullable|string',
                'location_id' => 'nullable|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
            }
            $est->update($validator->validated());
            if ($request->has('camps')) {
                $camps = Camp::find($request->camps);
                if (!$camps) {
                    return response()->json(["message" => "camp not found"], 404);
                }
                $est->Camp()->sync($camps);
            }
            return response()->json(["message" => "ElectricalMeter updated successfully", "data" => $est], 200);
        }
    }

    public function destroy($id)
    {
        $est = ElectricalMeter::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $est->delete();
            return response()->json(["message" => "ElectricalMeter deleted successfully"], 200);
        }
    }
}

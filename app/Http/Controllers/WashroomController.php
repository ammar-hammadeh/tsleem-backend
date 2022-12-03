<?php

namespace App\Http\Controllers;

use App\Models\Camp;
use App\Models\Location;
use App\Models\Washroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WashroomController extends Controller
{
    public function index()
    {
        $est = Washroom::with('Location', 'Camp')->get();
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
            'wc_number' => 'nullable|string',
            'portable_wc_count' => 'nullable|string',
            'wc_category' => 'nullable|string',
            'located_in_gov_area' => 'nullable|integer',
            'toilets_count' => 'nullable|string',
            'internal_water_tapes_count' => 'nullable|string',
            'external_water_tapes_count' => 'nullable|string',
            'total_water_tapes_count' => 'nullable|string',
            'seated_toilets_count' => 'nullable|string',
            'urinal_tapes_count' => 'nullable|string',
            'showers_count' => 'nullable|string',
            'upper_water_tank' => 'nullable|string',
            'location_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $est = Washroom::create($validator->validated());
        return response()->json(["message" => "Washroom added successfully", "data" => $est], 200);
    }


    public function storeWitCamps(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wc_number' => 'nullable|string',
            'portable_wc_count' => 'nullable|string',
            'wc_category' => 'nullable|string',
            'located_in_gov_area' => 'nullable|integer',
            'toilets_count' => 'nullable|string',
            'internal_water_tapes_count' => 'nullable|string',
            'external_water_tapes_count' => 'nullable|string',
            'total_water_tapes_count' => 'nullable|string',
            'seated_toilets_count' => 'nullable|string',
            'urinal_tapes_count' => 'nullable|string',
            'showers_count' => 'nullable|string',
            'upper_water_tank' => 'nullable|string',
            'location_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $est = Washroom::create($validator->validated());
        if ($request->has('camps')) {
            $camps = Camp::find($request->camps);
            if (!$camps) {
                return response()->json(["message" => "camp not found"], 404);
            }
            $est->Camp()->sync($camps);
        }
        return response()->json(["message" => "Washroom added successfully", "data" => $est], 200);
    }

    public function edit($id)
    {
        $est = Washroom::with('Camp')->find($id);
        if (!$est) {
            return response()->json(["message" => "Data not found"], 500);
        }
        $camps = Camp::all();
        $locations = Location::all();
        return response()->json(["data" => $est, 'camps' => $camps, 'locations' => $locations], 200);
    }
    public function update(Request $request, $id)
    {
        $est = Washroom::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $validator = Validator::make($request->all(), [
                'wc_number' => 'nullable|string',
                'portable_wc_count' => 'nullable|string',
                'wc_category' => 'nullable|string',
                'located_in_gov_area' => 'nullable|integer',
                'toilets_count' => 'nullable|string',
                'internal_water_tapes_count' => 'nullable|string',
                'external_water_tapes_count' => 'nullable|string',
                'total_water_tapes_count' => 'nullable|string',
                'seated_toilets_count' => 'nullable|string',
                'urinal_tapes_count' => 'nullable|string',
                'showers_count' => 'nullable|string',
                'upper_water_tank' => 'nullable|string',
                'location_id' => 'nullable|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
            }
            $est->update($validator->validated());
            return response()->json(["message" => "Washroom updated successfully", "data" => $est], 200);
        }
    }

    public function updateWithCamps(Request $request, $id)
    {
        $est = Washroom::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $validator = Validator::make($request->all(), [
                'wc_number' => 'nullable|string',
                'portable_wc_count' => 'nullable|string',
                'wc_category' => 'nullable|string',
                'located_in_gov_area' => 'nullable|integer',
                'toilets_count' => 'nullable|string',
                'internal_water_tapes_count' => 'nullable|string',
                'external_water_tapes_count' => 'nullable|string',
                'total_water_tapes_count' => 'nullable|string',
                'seated_toilets_count' => 'nullable|string',
                'urinal_tapes_count' => 'nullable|string',
                'showers_count' => 'nullable|string',
                'upper_water_tank' => 'nullable|string',
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
            return response()->json(["message" => "Washroom updated successfully", "data" => $est], 200);
        }
    }

    public function destroy($id)
    {
        $est = Washroom::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $est->delete();
            return response()->json(["message" => "Washroom deleted successfully"], 200);
        }
    }
}

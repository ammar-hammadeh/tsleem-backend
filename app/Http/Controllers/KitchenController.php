<?php

namespace App\Http\Controllers;

use App\Models\Camp;
use App\Models\Kitchen;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KitchenController extends Controller
{
    public function index()
    {
        $est = Kitchen::with('Location', 'Camp')->get();
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
        $est = Kitchen::create($validator->validated());
        return response()->json(["message" => "Kitchen added successfully", "data" => $est], 200);
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
        $est = Kitchen::create($validator->validated());
        if ($request->has('camps')) {
            $camps = Camp::find($request->camps);
            if (!$camps) {
                return response()->json(["message" => "camp not found"], 404);
            }
            $est->Camp()->sync($camps);
        }
        return response()->json(["message" => "Kitchen added successfully", "data" => $est], 200);
    }

    public function edit($id)
    {
        $est = Kitchen::with('Camp')->find($id);
        if (!$est) {
            return response()->json(["message" => "Data not found"], 500);
        }
        $locations = Location::all();
        $camps = Camp::all();
        return response()->json(["data" => $est, 'locations' => $locations, 'camps' => $camps], 200);
    }

    public function update(Request $request, $id)
    {
        $est = Kitchen::find($id);
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
            return response()->json(["message" => "Kitchen updated successfully", "data" => $est], 200);
        }
    }

    public function updateWithCamps(Request $request, $id)
    {
        $est = Kitchen::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'location_id' => 'required|integer',
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
            return response()->json(["message" => "Kitchen updated successfully", "data" => $est], 200);
        }
    }
    public function destroy($id)
    {
        $est = Kitchen::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $est->delete();
            return response()->json(["message" => "Kitchen deleted successfully"], 200);
        }
    }
}

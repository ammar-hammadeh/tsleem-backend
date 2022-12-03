<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Models\Plot;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlotController extends Controller
{
    public function index()
    {
        $est = Plot::with('Zone', 'Establishment')->get();
        return response()->json(['data' => $est], 200);
    }
    public function get_data()
    {
        $establishments = Establishment::all();
        $zones = Zone::all();
        return response()->json(['establishments' => $establishments, 'zones' => $zones], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plot_number' => 'nullable|string',
            'zone_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $est = Plot::create($validator->validated());
        return response()->json(["message" => "Plot added successfully", "data" => $est], 200);
    }

    public function storeWithEstablishments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plot_number' => 'nullable|string',
            'zone_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $est = Plot::create($validator->validated());
        if ($request->has('establishments')) {
            $establishments = Establishment::find($request->establishments);
            if (!$establishments) {
                return response()->json(["message" => "establishments not found"], 404);
            }
            $est->Establishment()->sync($establishments);
        }
        return response()->json(["message" => "Plot added successfully", "data" => $est], 200);
    }

    public function edit($id)
    {
        $est = Plot::with('Zone')->find($id);
        if (!$est) {
            return response()->json(["message" => "Data not found"], 500);
        }
        $establishments = Establishment::all();
        $zones = Zone::all();
        return response()->json(["data" => $est, 'establishments' => $establishments, 'zones' => $zones], 200);
    }

    public function update(Request $request, $id)
    {
        $est = Plot::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $validator = Validator::make($request->all(), [
                'plot_number' => 'nullable|string',
                'zone_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
            }
            $est->update($validator->validated());
            return response()->json(["message" => "Plot updated successfully", "data" => $est], 200);
        }
    }

    public function updateWithEstablishments(Request $request, $id)
    {
        $est = Plot::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $validator = Validator::make($request->all(), [
                'plot_number' => 'nullable|string',
                'zone_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
            }
            $est->update($validator->validated());
            if ($request->has('establishments')) {
                $establishments = Establishment::find($request->establishments);
                if (!$establishments) {
                    return response()->json(["message" => "establishments not found"], 404);
                }
                $est->Establishment()->sync($establishments);
            }
            return response()->json(["message" => "Plot updated successfully", "data" => $est], 200);
        }
    }
    public function destroy($id)
    {
        $est = Plot::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $est->delete();
            return response()->json(["message" => "Plot deleted successfully"], 200);
        }
    }
}

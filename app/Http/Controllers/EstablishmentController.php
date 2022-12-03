<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Models\Plot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EstablishmentController extends Controller
{
    //
    public function index()
    {
        $est = Establishment::with('Plot')->get();
        return response()->json(['data' => $est], 200);
    }
    public function get_data()
    {
        $plots = Plot::all();
        return response()->json(['plots' => $plots], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'color' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $est = Establishment::create($validator->validated());
        return response()->json(["message" => "Establishment added successfully", "data" => $est], 200);
    }


    public function storeWithPlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'color' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $est = Establishment::create($validator->validated());
        if ($request->has('plots')) {
            $plots = Plot::find($request->plots);
            if (!$plots) {
                return response()->json(["message" => "plots not found"], 404);
            }
            $est->Plot()->sync($plots);
        }
        return response()->json(["message" => "Establishment added successfully", "data" => $est], 200);
    }

    public function edit($id)
    {
        $est = Establishment::with('Plot')->find($id);
        if (!$est) {
            return response()->json(["message" => "Data not found"], 500);
        }
        $plots = Plot::all();
        return response()->json(["data" => $est, 'plots' => $plots], 200);
    }

    public function update(Request $request, $id)
    {
        $est = Establishment::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'color' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
            }
            $est->update($validator->validated());
            return response()->json(["message" => "Establishment updated successfully", "data" => $est], 200);
        }
    }

    public function updateWithPlots(Request $request, $id)
    {
        $est = Establishment::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'color' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
            }
            $est->update($validator->validated());
            if ($request->has('plots')) {
                $plots = Plot::find($request->plots);
                if (!$plots) {
                    return response()->json(["message" => "plots not found"], 404);
                }
                $est->Plot()->sync($plots);
            }
            return response()->json(["message" => "Establishment updated successfully", "data" => $est], 200);
        }
    }
    public function destroy($id)
    {
        $est = Establishment::find($id);
        if ($est == null) {
            return response()->json(["message" => "Data not found"], 500);
        } else {
            $est->delete();
            return response()->json(["message" => "Establishment deleted successfully"], 200);
        }
    }
}

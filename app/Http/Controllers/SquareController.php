<?php

namespace App\Http\Controllers;

use App\Models\Square;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SquareController extends Controller
{
    public function filters()
    {
        $filters = [
            [
                'name' => 'start',
                'value' => '',
                'label' => __('general.Start'),
                'type' => 'date',
                'items' => ''
            ],
            [
                'name' => 'end',
                'value' => '',
                'label' => __('general.End'),
                'type' => 'date',
                'items' => ''
            ],
            [
                'name' => 'name',
                'value' => '',
                'label' => __('general.Name'),
                'type' => 'text',
                'items' => ''
            ],

        ];
        return $filters;
    }

    // private function filterData($request)
    // {
    //     $data = 1;
    //     if ($request->input('square_name')) {
    //         $data .= " and name = '" . $request->input('square_name') . "'";
    //     }
    //     if ($request->start) {
    //         $data .= " and DATE(camps.created_at) >= '" . $request->from . "'";
    //     }
    //     if ($request->end) {
    //         $data .= " and DATE(camps.created_at) <= '" . $request->to . "'";
    //     }
    //     return $data;
    // }

    public function index(Request $request)
    {
        $paginate = env('PAGINATE');
        if ($request->has('paginate')) {
            $paginate = $request->paginate;
        }

        $query = Square::query();
        if ($request->start != '')
            $query->whereDate('created_at', '>=', $request->start);
        if ($request->end != '')
            $query->whereDate('created_at', '<=', $request->end);
        if ($request->name != '')
            $query->where('name', 'like', '%' . $request->name . '%');

        $squares = $query->paginate($paginate);
        return response()->json(["data" => $squares, 'filters' => $this->filters()], 200);
    }
    //
    public function edit($id)
    {
        $Square = Square::find($id);
        if (!$Square) {
            return response()->json(["message" => "not found"], 404);
        }
        return response()->json(["data" => $Square], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:square',
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $res = Square::create($request->input());
            DB::commit();
            if ($res) {
                return response()->json(["message" => "add new square", "type" => $res], 200);
            } else {
                return response()->json(["message" => "an error occurred"], 500);
            }
        } catch (\Exception $e) {
            return response()->json(["message" => "square updated fail", "error" => $e], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:square',
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $square = Square::find($id);
        if ($square) {

            DB::beginTransaction();
            try {
                $square->update($request->input());
                DB::commit();
                return response()->json(["message" => "square updated successfully"], 200);
            } catch (\Exception $e) {
                return response()->json(["message" => "square updated fail", "error" => $e], 500);
            }
        } else {
            return response()->json(["message" => "square id not found", "error" => "id is not correct"], 500);
        }
    }

    public function delete($id)
    {
        // if ($this->validateParameterId($id)) {
        //     return response()->json(["message" => "id should be integer"], 500);
        // }
        $type = Square::find($id);
        if ($type == null) {
            return response()->json(["message" => "المربع غير موجود"], 500);
        }
        DB::beginTransaction();
        try {
            $res = $type->delete();
            DB::commit();
            if ($res) {
                return response()->json(["message" => "success delete square", "square" => $type], 200);
            } else {
                return response()->json(["message" => "can't delete square",], 500);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e], 500);
        }
    }
}

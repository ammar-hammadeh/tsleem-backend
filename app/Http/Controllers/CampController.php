<?php

namespace App\Http\Controllers;

use App\Models\Camp;
use App\Models\Square;
use App\Models\Company;
use App\Models\AssignCamp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CampController extends Controller
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
            [
                'name' => 'square',
                'value' => '',
                'label' => __('general.Square'),
                'type' => 'auto-complete',
                'items' => Square::get(),
                'itemText' => 'name',
                'itemValue' => 'id'
            ],
            [
                'name' => 'status',
                'value' => '',
                'label' => __('general.status'),
                'type' => 'select',
                'items' => [
                    ['name' => 'ready', 'label' => __('general.Ready')],
                    ['name' => 'not_ready', 'label' => __('general.Not Ready')]
                ],
                'itemText' => 'label',
                'itemValue' => 'name'
            ],

        ];
        return $filters;
    }

    private function filterData($request)
    {
        $data = 1;
        if ($request->input('camp_name')) {
            $data .= " and name = '" . $request->input('camp_name') . "'";
        }
        if ($request->input('status')) {
            $data .= " and status = '" . $request->input('status') . "'";
        }
        if ($request->start) {
            $data .= " and DATE(camps.created_at) >= '" . $request->from . "'";
        }
        if ($request->end) {
            $data .= " and DATE(camps.created_at) <= '" . $request->to . "'";
        }

        return $data;
    }

    public function index(Request $request)
    {
        $paginate = env('PAGINATE');
        if ($request->has('paginate')) {
            $paginate = $request->paginate;
        }
        $where = $this->filterData($request);

        if (!$where) {
            $camps = Camp::with(['square'])
                ->paginate($paginate);
            return response()->json(["data" => $camps, 'filters' => $this->filters()], 200);
        }
        $camps = Camp::with(['square'])
            ->whereRaw($where)
            ->paginate($paginate);
        return response()->json(["data" => $camps, 'filters' => $this->filters()], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:camps',
            'square_id' => 'required|int|between:0,9223372036854775807',
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $res = Camp::create($request->input());
            DB::commit();
            if ($res) {
                return response()->json(["message" => "add new camp", "type" => $res], 200);
            } else {
                return response()->json(["message" => "an error occurred"], 500);
            }
        } catch (\Exception $e) {
            return response()->json(["message" => "camp updated fail", "error" => $e], 500);
        }
    }

    public function edit($id)
    {
        $camp = Camp::find($id);
        if (!$camp) {
            return response()->json(["message" => "not found"], 404);
        }
        return response()->json(["data" => $camp], 200);
    }

    public function get_square($id)
    {
        $Square = Square::find($id);
        if (!$Square) {
            return response()->json(["message" => "not found"], 404);
        }
        return response()->json(["data" => $Square], 200);
    }

    public function getData($id)
    {
        $Square = Square::get();
        return response()->json(["squares" => $Square], 200);
    }

    public function update(Request $request, $id)
    {
        if (!$id) {
            return response()->json(["message" => "Please Check errors", "errors" => "id isn't input"], 422);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'string|unique:camps',
            'square_id' => 'int|between:0,9223372036854775807',
            'status' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        $type = Camp::find($id);
        if ($type) {
            DB::beginTransaction();
            try {
                Camp::where('id', $id)
                    ->update(["status" => $request->input('status')]);
                DB::commit();
                return response()->json(["message" => "camp updated successfully"], 200);
            } catch (\Exception $e) {
                return response()->json(["message" => "camp updated fail", "error" => $e], 500);
            }
        } else {
            return response()->json(["message" => "camp id not found", "error" => "id is not correct"], 500);
        }
    }


    public function updateCampStatus($id)
    {
        $camp = Camp::find($id);
        if (!$camp)
            return response()->json(["message" => "please check the camp id and try again"], 204);

        if ($camp->status == 'notready')
            $camp->update(['status' => 'ready']);
        else
            $camp->update(['status' => 'notready']);

        return response()->json(["data" => $camp], 200);
    }

    public function delete($id)
    {
        if ($id) {
            return response()->json(["message" => "error input", "error" => "id should be integer"], 500);
        }
        $type = Camp::find($id);
        DB::beginTransaction();
        try {
            $res = $type->delete();
            DB::commit();
            if ($res) {
                return response()->json(["message" => "success delete camp", "camp" => $type], 200);
            } else {
                return response()->json(["message" => "can't delete camp",], 500);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message" => "Please check errors", "errors" => $e], 500);
        }
    }


    public function showCompanyReadyCamps()
    {
        $paginate = env('PAGINATE');
        $user = Auth::user();
        $company = Company::where('owner_id', $user->id)->first();
        $assign_camps = AssignCamp::whereNull('deleted')
            ->where('assigner_company_id', $company->id)
            ->pluck('camp_id')->toArray();
        $camps = Camp::whereIn('id', $assign_camps)->where('status', '!=', 'notready')->paginate($paginate);
        return response()->json($camps, 200);
    }

    public function CampBySquare($id)
    {
        $assignCamp = AssignCamp::pluck('camp_id');
        $camps = Camp::where('square_id', $id)
            // ->where('status', 'ready')
            ->whereNotIn('id', $assignCamp)
            ->get();
        return response()->json($camps, 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Camp;
use App\Models\Square;
use App\Models\Company;
use App\Models\Location;
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
                    ['name' => 'notready', 'label' => __('general.Not Ready')]
                ],
                'itemText' => 'label',
                'itemValue' => 'name'
            ],

        ];
        return $filters;
    }

    // private function filterData($request)
    // {
    //     $data = 1;
    //     if ($request->input('camp_name')) {
    //         $data .= " and name = '" . $request->input('camp_name') . "'";
    //     }
    //     if ($request->input('status')) {
    //         $data .= " and status = '" . $request->input('status') . "'";
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

        $camps = Camp::with('square', 'Location', 'Establishment_plots');
        if ($request->start != '')
            $camps->whereDate('created_at', '>=', $request->start);
        if ($request->end != '')
            $camps->whereDate('created_at', '<=', $request->end);
        if ($request->name != '')
            $camps->where('name', 'like', '%' . $request->name . '%');
        if ($request->square != '') {
            $square_id = $request->square;
            $camps->whereHas('square', function ($query) use ($square_id) {
                $query->where('id', $square_id);
            });
        }
        if ($request->location != '') {
            $location_id = $request->location;
            $camps->whereHas('Location', function ($query) use ($location_id) {
                $query->where('id', $location_id);
            });
        }
        if ($request->establishment_plots != '') {
            $est_plot_lookup_id = $request->establishment_plots;
            $camps->whereHas('Establishment_plots', function ($query) use ($est_plot_lookup_id) {
                $query->where('id', $est_plot_lookup_id);
            });
        }

        // else
        //     $camps->;

        if ($request->status != '')
            $camps->where('status', $request->status);

        $data = $camps->paginate($paginate);
        return response()->json(["data" => $data, 'filters' => $this->filters()], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:camps',
            'square_id' => 'required|int|between:0,9223372036854775807',
            'gate' => 'nullable|string|max:5',
            'street' => 'nullable|string|max:5',
            'developed_name' => 'nullable|string|max:25'
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $res = Camp::create($request->input());
            // $user_id = Auth::user()->type_id;
            // $note = __('general.addCampLog') . $res->name;
            // LogHelper::storeLog($user_id, 33, $note);
            DB::commit();
            // if ($res) {
            return response()->json(["message" => "add new camp", "type" => $res], 200);
            // } else {
            // return response()->json(["message" => "an error occurred"], 500);
            // }
        } catch (\Exception $e) {
            return response()->json(["message" => "camp created fail", "error" => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $camp = Camp::find($id);
        if (!$camp) {
            return response()->json(["message" => "not found"], 404);
        }
        $squares = Square::get();
        $locations = Location::get();
        return response()->json(["data" => $camp, 'squares' => $squares, 'locations' => $locations], 200);
    }

    public function get_square($id)
    {
        $Square = Square::find($id);
        if (!$Square) {
            return response()->json(["message" => "not found"], 404);
        }
        return response()->json(["data" => $Square], 200);
    }

    public function getData()
    {
        $Square = Square::get();
        $locations = Location::get();
        return response()->json(["squares" => $Square, 'locations' => $locations], 200);
    }

    public function update(Request $request, $id)
    {
        if (!$id) {
            return response()->json(["message" => "Please Check errors", "errors" => "id isn't input"], 422);
        }
        $camp = Camp::find($id);
        $validator = Validator::make($request->all(), [
            'name' => 'string|unique:camps,name,' . $camp->id,
            'square_id' => 'int|between:0,9223372036854775807',
            'gate' => 'nullable|string|max:5',
            'street' => 'nullable|string|max:5',
            'developed_name' => 'nullable|string|max:25'
            // 'status' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(["message" => "Please Check errors", "errors" => $validator->errors()], 422);
        }
        // $camp = Camp::find($id);
        if ($camp) {
            DB::beginTransaction();
            try {

                $camp->update($request->input());
                // $user_id = Auth::user()->type_id;
                // $note = __('general.updateCampLog') . $camp->name;
                // LogHelper::storeLog($user_id, 34, $note);

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
        // $user_id = Auth::user()->type_id;
        // $note = __('general.changeStatusCampLog') . $camp->name;
        // LogHelper::storeLog($user_id, 36, $note);

        return response()->json(["data" => $camp], 200);
    }

    public function delete($id)
    {
        $camp = Camp::find($id);
        if (!$camp)
            return response()->json(["message" => "المخيم غير موجود"], 500);
        $camp->delete();

        // $user_id = Auth::user()->type_id;
        // $note = __('general.deleteCampLog') . $camp->name;
        // LogHelper::storeLog($user_id, 35, $note);

        return response()->json(["message" => "تم حذف المخيم"]);
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

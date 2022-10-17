<?php

namespace App\Http\Controllers;

use App\Models\Camp;
use App\Models\Type;
use App\Models\Square;
use App\Models\Company;
use App\Models\AssignCamp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AssignCampsRequest;

class AssignCampController extends Controller
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
                'name' => 'receiver_cr',
                'value' => '',
                'label' => __('general.Name'),
                'type' => 'text',
                'items' => ''
            ],
            [
                'name' => 'receiver_company_id',
                'value' => '',
                'label' => __('general.CR Number'),
                'type' => 'auto-complete',
                'items' => Company::get(),
                'itemText' => 'commercial',
                'itemValue' => 'id'
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
                'name' => 'camp',
                'value' => '',
                'label' => __('general.Camp'),
                'type' => 'auto-complete',
                'items' => Camp::get(),
                'itemText' => 'name',
                'itemValue' => 'id'
            ],
            [
                'name' => 'status',
                'value' => '',
                'label' => __('general.status'),
                'type' => 'select',
                'items' => [
                    ['name' => 'pending', 'label' => __('general.pending')],
                    ['name' => 'returned', 'label' => __('general.Returned')],
                    ['name' => 'appointment', 'label' => __('general.Appointment')],
                    ['name' => 'deliverd', 'label' => __('general.Deliverd')]
                ],
                'itemText' => 'label',
                'itemValue' => 'name'
            ],

        ];
        return $filters;
    }

    public function editCampAssign($id)
    {
        $assign = AssignCamp::find($id);
        if (!$assign) {
            return response()->json(["message" => "not found"], 404);
        }
        $squares = Square::all();
        $companies = Company::select('id', 'name')->get();
        $camps = Camp::where('square_id', $assign->square_id)->get();
        return response()->json(["data" => $assign, "camps" => $camps, "companies" => $companies, 'squares' => $squares], 200);
    }

    public function index(Request $request)
    {

        if ($request->has('paginate')) {
            $paginate = $request->paginate;
        } else
            $paginate = env('PAGINATE');

        $assignedCampsCR =  AssignCamp::with('getSquare', 'getCamp')
            ->leftJoin('companies', 'companies.license', 'assign_camps.receiver_cr');

        $assignedCampsID =  AssignCamp::with('getSquare', 'getCamp')
            ->leftJoin('companies', 'companies.id', 'assign_camps.receiver_company_id');

        //check user type
        if (Auth::guard('api')->check()) {
            $userTypeID = Auth::user()->type_id;
            $userType = Type::where('id', $userTypeID)->value('code');
            $userCompany = Company::where('owner_id', Auth::user()->id)->value('commercial');
            if ($userType != 'admin') {
                $assignedCampsCR->where('assigner_cr', $userCompany);
                $assignedCampsID->where('assigner_cr', $userCompany);
            }
        }

        if ($request->start != '') {
            $assignedCampsID->whereDate('created_at', '>=', $request->start);
            $assignedCampsCR->whereDate('created_at', '>=', $request->start);
        }
        if ($request->end != '') {
            $assignedCampsID->whereDate('created_at', '<=', $request->end);
            $assignedCampsCR->whereDate('created_at', '<=', $request->end);
        }
        if ($request->status != '') {
            $assignedCampsID->where('status', $request->status);
            $assignedCampsCR->where('status', $request->status);
        }
        if ($request->receiver_cr != '') {
            $assignedCampsID->where('receiver_cr', $request->receiver_cr);
            $assignedCampsCR->where('receiver_cr', $request->receiver_cr);
        }
        if ($request->receiver_company_id != '') {
            $assignedCampsID->where('receiver_company_id', $request->receiver_company_id);
            $assignedCampsCR->where('receiver_company_id', $request->receiver_company_id);
        }
        if ($request->square != '') {
            $assignedCampsID->where('square_id', $request->square_id);
            $assignedCampsCR->where('square_id', $request->square_id);
        }
        if ($request->camp != '') {
            $assignedCampsID->where('camp_id', $request->camp_id);
            $assignedCampsCR->where('camp_id', $request->camp_id);
        }

        $query1 = $assignedCampsID->select('assign_camps.id as id','assign_camps.*','companies.name')->paginate($paginate);
        // $query2 = $assignedCampsCR->get();
        // $result = $query1->merge($query2);
        // $r = $query1->paginate($paginate);
        return response()->json(["data" => $query1, 'filters' => $this->filters()]);
    }

    public function assignCampToCompany(AssignCampsRequest $request)
    {
        $check = AssignCamp::where('square_id', $request->square_id)
            ->where('camp_id', $request->camp_id)->first();
        if ($check)
            return response()->json('this camp is already assigned to another company', 500);

        $company = Company::select('companies.id', 'license', 'code')
            ->where('license', $request->receiver_cr)
            ->orWhere('companies.id', $request->receiver_company_id)
            ->join('types', 'types.id', 'companies.type_id')
            ->first();

        $parent = null;
        $cr = null;
        $id = null;

        if ($company) {
            if ($company->code == 'raft_company')
                $parent = $company->license;
            $id = $company->id;
        } else {
            if (!$request->receiver_cr)
                return response()->json(['message' => 'please enter valid CR number'], 500);
            $cr = $request->receiver_cr;
        }

        $data = [
            'assigner_cr' => $parent,
            'receiver_cr' => $cr,
            'receiver_company_id' => $id,
            'square_id' => $request->square_id,
            'camp_id' => $request->camp_id,
        ];
        AssignCamp::create($data);
        return response()->json(['message' => 'camp assigned successfully']);
    }

    public function getData()
    {
        $data = $this->get_data();
        return response()->json(["companies" => $data['companies'], 'squares' => $data['squares']]);
    }

    private function get_data()
    {
        $squares = Square::all();
        $companies = Company::select('id', 'name')->get();
        return ["companies" => $companies, 'squares' => $squares];
    }


    public function updateCampAssignation($id, AssignCampsRequest $request)
    {

        $camp = AssignCamp::find($id);
        if (!$camp)
            return response()->json('please check the assignation id and try again', 500);

        $parent = Company::where('commercial', $request->receiver_cr)
            ->join('types', 'types.id', 'companies.type_id')
            ->where('types.code', 'raft_office')->value('license');

        $data = [
            'assigner_cr' => $parent,
            'receiver_cr' => $request->receiver_cr,
            'receiver_company_id' => $request->receiver_company_id,
            'square_id' => $request->square_id,
            'camp_id' => $request->camp_id,
        ];
        $camp->update($data);

        return response()->json(['message' => 'the assignation has been updated successfully'], 200);
    }

    public function editCampByCompany($id)
    {
        $assign = AssignCamp::find($id);
        if (!$assign) {
            return response()->json(["message" => "not found"], 404);
        }
        $companies = null;
        if (Auth::guard('api')->check()) {
            $companies = Company::where('owner_id', Auth::user()->id)->get();
        }
        return response()->json(['data' => $assign, 'companies' => $companies]);
    }

    public function updateCampByCompany($id, Request $request)
    {

        $camp = AssignCamp::find($id);
        if (!$camp)
            return response()->json('please check the assignation id and try again', 500);

        $data = [
            'receiver_cr' => null,
            'receiver_company_id' => $request->receiver_company_id,
        ];
        $camp->update($data);

        return response()->json(['message' => 'the assignation has been updated successfully'], 200);
    }


    public function deleteCampAssignation($id)
    {

        $camp = AssignCamp::find($id);
        if (!$camp)
            return response()->json('please check the assignation id and try again', 500);

        $camp->delete();
        return response()->json(['message' => 'Assignation has been deleted successfully'], 200);
    }

    public function fixAssignation()
    {
        $companies = Company::get();
        foreach ($companies as $company) {
            AssignCamp::where('receiver_cr', $company->license)->update([
                'receiver_company_id' => $company->id,
                'receiver_cr' => null
            ]);
        }
        return response()->json('Done!!');
    }
}

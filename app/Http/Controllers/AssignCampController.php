<?php

namespace App\Http\Controllers;

use App\Models\Camp;
use App\Models\Type;
use App\Models\Square;
use App\Models\Company;
use App\Helper\LogHelper;
use App\Models\AssignCamp;
use Illuminate\Http\Request;
use Modules\Core\Entities\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AssignCampsRequest;
use App\Http\Requests\UpdateAssignCampsRequest;

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
                'name' => 'receiver_company_id',
                'value' => '',
                'label' => __('general.company_name'),
                'type' => 'auto-complete',
                'items' => Company::whereHas('Type', function ($query) {
                    $query->whereIn('code', ['raft_company', 'service_provider', 'raft_office']);
                })->get(),
                'itemText' => 'name',
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
        $companies = Company::with('Type')->get();
        $assignCamp = AssignCamp::where('camp_id', '!=', $assign->camp_id)->pluck('camp_id');
        $camps = Camp::where('square_id', $assign->square_id)
            ->whereNotIn('id', $assignCamp)
            // ->where('id', $assign->camp_id)
            ->get();
        return response()->json(["data" => $assign, "camps" => $camps, "companies" => $companies, 'squares' => $squares], 200);
    }

    public function index(Request $request)
    {

        if ($request->has('paginate')) {
            $paginate = $request->paginate;
        } else
            $paginate = env('PAGINATE');

        $assignedCampsID =  AssignCamp::with('getSquare', 'getCamp', 'getCompany.Type');

        //check user type
        if (Auth::guard('api')->check()) {
            $userType = Type::where('id', Auth::user()->type_id)->value('code');
            // $userCompany = Company::where('id', Auth::user()->company_id)->value('license');
            if ($userType != 'admin' && $userType != 'raft_company') {
                $assignedCampsID->where('receiver_company_id', Auth::user()->company_id);
            } elseif ($userType == 'raft_company') {
                // dd();
                $assignedCampsID->where('assigner_company_id', Auth::user()->company_id);
            }
        }

        if ($request->start != '')
            $assignedCampsID->whereDate('created_at', '>=', $request->start);
        if ($request->end != '')
            $assignedCampsID->whereDate('created_at', '<=', $request->end);
        if ($request->status != '')
            $assignedCampsID->where('status', $request->status);
        if ($request->receiver_company_id != '') {
            $company_id = $request->receiver_company_id;
            $assignedCampsID->whereHas('getCompany', function ($query) use ($company_id) {
                $query->where('id', $company_id);
            });
        }

        if ($request->square != '') {
            $square_id = $request->square;
            $assignedCampsID->whereHas('getSquare', function ($query) use ($square_id) {
                $query->where('id', $square_id);
            });
        }

        if ($request->camp != '') {
            $camp_id = $request->camp;
            $assignedCampsID->whereHas('getCamp', function ($query) use ($camp_id) {
                $query->where('id', $camp_id);
            });
        }

        $assignedCamps = $assignedCampsID->paginate($paginate);

        return response()->json(["data" => $assignedCamps, 'filters' => $this->filters()]);
    }

    public function assignCampToCompany(AssignCampsRequest $request)
    {
        $check = AssignCamp::where('square_id', $request->square_id)
            ->where('camp_id', $request->camp_id)->first();
        if ($check)
            return response()->json('this camp is already assigned to another company', 500);

        $assigner_company_id = null;
        $userTypeID = Auth::user()->type_id;
        $userType = Type::where('id', $userTypeID)->value('code');

        $company = Company::select('companies.id', 'companies.type_id', 'license', 'code', 'parent_id', 'owner_id')
            ->where('companies.id', $request->receiver_company_id)
            ->join('types', 'types.id', 'companies.type_id')
            ->first();
        $companyType = Type::where('id', $company->type_id)->value('code');
        // return response()->json($company);
        if ($companyType == 'raft_company') {
            $assigner_company_id =  $company->id;
        }
        if ($userType == 'admin' && $companyType == 'raft_office') {
            $comp_parent = Company::find($company->parent_id);
            $user = User::find($comp_parent->owner_id);
            $assigner_company_id =  $user->company_id;
        }

        foreach ($request->camp_id as $camp_id) {
            $data = [
                'assigner_cr' => $company->license,
                'receiver_company_id' => $request->receiver_company_id,
                'assigner_company_id' => $assigner_company_id,
                'square_id' => $request->square_id,
                'camp_id' => $camp_id,
                'contract_status' => 'signed'
            ];
            $new = AssignCamp::create($data);

            $assignation = AssignCamp::with('getCompany', 'getCamp', 'getSquare')->find($new->id);
            $user_id = Auth::user()->id;
            $old_value = null;
            $new_value = [
                'company' => $assignation->getCompany->name,
                'square' => $assignation->getSquare->name,
                'camp' => $assignation->getCamp->name,
            ];
            $module = 'assignCamps';
            $method_id = 1;
            $message = __('logTr.addCamp');

            LogHelper::storeLog(
                $user_id,
                json_decode(json_encode($old_value)),
                json_decode(json_encode($new_value)),
                $module,
                $method_id,
                $message,
            );

            $notificationMessage = __('general.newAssignation');
            $link = "/assign";
            (new NotificationController)->addNotification($company->owner_id, $notificationMessage, $link);
        }

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

        //check user type
        if (Auth::guard('api')->check()) {
            $userTypeID = Auth::user()->type_id;
            $userType = Type::where('id', $userTypeID)->value('code');
            if ($userType == 'admin')
                $companies = Company::with('Type')->get();
            else {
                $Ownercompany = Company::select('companies.id', 'license', 'code')
                    ->where('companies.owner_id', Auth::user()->id)
                    ->join('types', 'types.id', 'companies.type_id')
                    ->first();
                if ($Ownercompany)
                    if ($Ownercompany->code == 'raft_company')
                        $companies = Company::with('Type')->where('parent_id', $Ownercompany->id)->get();
            }
        }

        return ["companies" => $companies, 'squares' => $squares];
    }


    public function updateCampAssignation($id, UpdateAssignCampsRequest $request)
    {

        $camp = AssignCamp::with('getCompany', 'getCamp', 'getSquare')->find($id);
        if (!$camp)
            return response()->json('please check the assignation id and try again', 500);

        $check = AssignCamp::where('square_id', $request->square_id)
            ->where('camp_id', $request->camp_id)
            ->where('id', '!=', $id)
            ->first();

        if ($check)
            return response()->json('this camp is already assigned to another company', 500);


        $userTypeID = Auth::user()->type_id;
        $userType = Type::where('id', $userTypeID)->value('code');

        $assigner_company_id = null;
        if ($userType == 'raft_company')
            $assigner_company_id = Auth::user()->company_id;

        $data = [
            // 'assigner_cr' => $parent,
            'assigner_company_id' => $assigner_company_id,
            'receiver_company_id' => $request->receiver_company_id,
            'square_id' => $request->square_id,
            'camp_id' => $request->camp_id,
        ];

        // $assignation = AssignCamp::with('getCompany', 'getCamp', 'getSquare')->find();
        $old_value = [
            'company' => $camp->getCompany->name,
            'square' => $camp->getSquare->name,
            'camp' => $camp->getCamp->name,
        ];
        $camp->update($data);
        $user_id = Auth::user()->id;
        $new_value = [
            'company' => $camp->getCompany->name,
            'square' => $camp->getSquare->name,
            'camp' => $camp->getCamp->name,
        ];
        $module = 'assignCamps';
        $method_id = 2;
        $message = __('logTr.updateCamp');

        LogHelper::storeLog(
            $user_id,
            json_decode(json_encode($old_value)),
            json_decode(json_encode($new_value)),
            $module,
            $method_id,
            $message,
        );



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
            $Ownercompany = Company::select('companies.id', 'license', 'code')
                ->join('types', 'types.id', 'companies.type_id')
                ->where('companies.owner_id', Auth::user()->id)
                ->whereIn('types.code', ['raft_company', 'service_provider', 'raft_office'])
                ->first();

            if ($Ownercompany)
                if ($Ownercompany->code == 'raft_company')
                    $companies = Company::with('Type')->where('parent_id', $Ownercompany->id)->get();
        }
        return response()->json(['data' => $assign, 'companies' => $companies]);
    }

    public function updateCampByCompany($id, Request $request)
    {

        $camp = AssignCamp::find($id);
        if (!$camp)
            return response()->json('please check the assignation id and try again', 500);

        $userTypeID = Auth::user()->type_id;
        $userType = Type::where('id', $userTypeID)->value('code');

        $assigner_company_id = null;
        if ($userType == 'raft_company')
            $assigner_company_id = Auth::user()->company_id;

        $data = [
            'assigner_company_id' => $assigner_company_id,
            'receiver_company_id' => $request->receiver_company_id,
        ];
        $camp->update($data);

        return response()->json(['message' => 'the assignation has been updated successfully'], 200);
    }


    public function deleteCampAssignation($id)
    {

        $camp = AssignCamp::with('getCompany', 'getCamp', 'getSquare')->find($id);
        if (!$camp)
            return response()->json('please check the assignation id and try again', 500);

        $user_id = Auth::user()->id;
        $old_value = [
            'company' => $camp->getCompany->name,
            'square' => $camp->getSquare->name,
            'camp' => $camp->getCamp->name,
        ];
        $new_value = null;
        $module = 'assignCamps';
        $method_id = 3;
        $message = __('logTr.deleteCamp');

        LogHelper::storeLog(
            $user_id,
            json_decode(json_encode($old_value)),
            json_decode(json_encode($new_value)),
            $module,
            $method_id,
            $message,
        );


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

    public function SignedTsleemAssignCamps()
    {
        $signed_assign = AssignCamp::where('forms_status', 'signed')->get();
        return response()->json(['data' => $signed_assign], 200);
    }
}

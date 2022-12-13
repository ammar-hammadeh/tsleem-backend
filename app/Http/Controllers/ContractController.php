<?php

namespace App\Http\Controllers;

use App\Models\Camp;
use App\Models\Type;
use App\Models\Square;
use App\Models\Company;
use App\Models\Contract;
use App\Models\AssignCamp;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Modules\Core\Entities\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    //
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
                'name' => 'company',
                'value' => '',
                'label' => 'الشركة',
                'type' => 'auto-complete',
                'items' => Company::get(),
                'itemText' => 'name',
                'itemValue' => 'id'
            ],
            [
                'name' => 'status',
                'value' => '',
                'label' => __('general.status'),
                'type' => 'select',
                'items' => [
                    ['name' => 'signed', 'label' => __('general.signed')],
                    ['name' => 'unsigned', 'label' => __('general.unsigned')]
                ],
                'itemText' => 'label',
                'itemValue' => 'name'
            ],

        ];
        return $filters;
    }

    public function index(Request $request)
    {
        if ($request->has('paginate'))
            $paginate = $request->paginate;
        else
            $paginate = env('PAGINATE');

        $contract = Contract::join('assign_camps', 'assign_camps.id', 'contracts.assign_camps_id')
            ->join('square', 'assign_camps.square_id', 'square.id')
            ->join('camps', 'assign_camps.camp_id', 'camps.id')
            ->join('companies', 'companies.id', 'contracts.company_id')
            ->leftjoin('users', 'companies.id', 'users.company_id')
            ->select(
                'assign_camps.id as assign_camps_id',
                'assign_camps.created_at',
                'assign_camps.status',
                'receiver_company_id',
                'square.id as square_id',
                'square.name as square_name',
                'camps.id as camp_id',
                'camps.name as camp_name',
                'companies.name as company_name',
                'contracts.status',
                'contracts.id',
                'contracts.qr'
            );
        if ($request->start)
            $contract->whereDate('contracts.created_at', '>=', $request->start);
        if ($request->end)
            $contract->whereDate('contracts.created_at', '<=', $request->end);

        if ($request->square)
            $contract->where('square.id', $request->square);

        if ($request->camp)
            $contract->where('camps.id', $request->camp);

        if ($request->company)
            $contract->where('companies.id', $request->company);

        if ($request->status)
            $contract->where('status', $request->status);

        $user = Auth::user();
        $user_type = Type::find($user->type_id);
        if ($user_type->code != 'admin' && $user_type->code != 'kdana' && $user_type->code != 'sharer')
            $contract->where('contracts.company_id', $user->company_id);

        $data = $contract->paginate($paginate);

        return response()->json(['data' => $data, 'filters' => $this->filters()], 200);
    }

    public function store(Request $request)
    {
        $code = Str::random(30);
        while (Contract::where('qr', $code)->first() != null) {
            $code = Str::random(30);
        }
        $data = array();
        $data['qr'] = $code;
        $contract = Contract::create(array_merge(
            $request->input(),
            $data
        ));
        $assign_camp = AssignCamp::find($contract->assign_camps_id);
        if ($assign_camp != null) {
            $assign_camp->update([
                'contract_status' => 'unsigned'
            ]);
        }
        return response()->json(['contract_status' => 'unsigned'], 200);
    }
    public function view($id)
    {
        $contract = Contract::with('users', 'AssignCamps.getCamp', 'AssignCamps.getSquare', 'AssignCamps.getCompany', 'users', 'Ministry', 'Kidana', 'CompanyLicense.Type', 'Company.Type')->find($id);

        $square = Contract::join('assign_camps', 'assign_camps.id', 'contracts.assign_camps_id')
            ->join('square', 'assign_camps.square_id', 'square.id')
            ->join('camps', 'assign_camps.camp_id', 'camps.id')
            ->join('companies', 'companies.id', 'contracts.company_id')
            ->select(
                'square.name as square_name',
                'camps.name as camp_name',
                'companies.name as company_name',
            )->where('contracts.company_id', $contract->company_id)->get();


        if ($contract != null) {
            return response()->json(['data' => $contract, 'square_camps' => $square], 200);
        } else {
            return response()->json(['message' => 'لايوجد بيانات مطابقة'], 404);
        }
    }
    public function viewByCode($code)
    {
        $contract = Contract::where('qr', $code)->with('users', 'AssignCamps.getCamp', 'AssignCamps.getSquare', 'AssignCamps.getCompany', 'users', 'Ministry', 'Kidana', 'CompanyLicense.Type', 'Company.Type')->first();

        $square = Contract::join('assign_camps', 'assign_camps.id', 'contracts.assign_camps_id')
            ->join('square', 'assign_camps.square_id', 'square.id')
            ->join('camps', 'assign_camps.camp_id', 'camps.id')
            ->join('companies', 'companies.id', 'contracts.company_id')
            ->select(
                'square.name as square_name',
                'camps.name as camp_name',
                'companies.name as company_name',
            )->where('contracts.company_id', $contract->company_id)->get();
        if ($contract != null) {
            return response()->json(['data' => $contract, 'square_camps' => $square], 200);
        } else {
            return response()->json(['message' => 'لايوجد بيانات مطابقة'], 404);
        }
    }

    public function SignContract($id)
    {
        $contract = Contract::find($id);
        $type = Type::find(Auth::user()->type_id);
        if ($type->code == "kdana")
            $contract->update([
                'kidana' => Auth::user()->id
            ]);
        elseif ($type->code == "sharer")
            $contract->update([
                'ministry' => Auth::user()->id
            ]);
        elseif ($type->code != "admin")
            $contract->update([
                'user_id' => Auth::user()->id
            ]);

        if ($contract->user_id != null  && $contract->kidana != null) {
            try {
                DB::beginTransaction();
                $contract->update([
                    'status' => 'signed'
                ]);
                $assign_camp = AssignCamp::find($contract->assign_camps_id);
                if ($assign_camp != null) {
                    $assign_camp->update([
                        'contract_status' => 'signed'
                    ]);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return ['status' => 'false', 'error' => $e->getMessage(), 'message' => 'يوجد خطأ يرجى التأكد من البيانات', 'code' => 500];
            }
            return ['status' => 'true', 'message' => 'done', 'code' => 200];
        }
        return ['status' => 'true', 'message' => 'done', 'code' => 200];
    }

    public function destroy($id)
    {
        $contract = Contract::find($id);
        if ($contract) {
            $contract->delete();
            return response()->json(['message' => 'contract has been deleted']);
        } else {
            return response()->json(['message' => 'contract not deleted']);
        }
    }

    public function CheckQR($qr)
    {
        $contract = Contract::where('qr', $qr)->with('users', 'AssignCamps.getCamp', 'AssignCamps.getSquare', 'AssignCamps.getCompany', 'users', 'Ministry', 'Kidana', 'CompanyLicense.Type', 'Company.Type')->first();
        if ($contract != null)
            return response()->json(['data' => $contract, 'status' => true, 'message' => 'المحضر صالح']);
        else
            return response()->json(['data' => $contract, 'status' => false, 'message' => 'المحضر غير صالح']);
    }

    public function BulkSign(Request $request)
    {
        $type = Type::find(Auth::user()->type_id);
        if ($request->select_all) {
            $contractsID = Contract::pluck('id')->toArray();
            foreach ($contractsID as $id) {
                $contract = Contract::find($id);
                if ($type->code == "kdana") {
                    if ($contract->status != 'signed' && $contract->kidana == null)
                        $this->SignContract($id);
                } elseif ($type->code != "admin" && $type->code != "kdana") {
                    if ($contract->status != 'signed' && $contract->user_id == null) {
                        $result = $this->SignContract($id);
                        if ($result['status'] == "false") {
                            return response()->json(['message' => $result['message'], 'error' => $result['error']], 500);
                        }
                    }
                }
            }
        } else {
            foreach ($request->ids as $id) {
                $contract = Contract::find($id);
                if ($type->code == "kdana") {
                    if ($contract->status != 'signed' && $contract->kidana == null)
                        $this->SignContract($id);
                } elseif ($type->code != "admin" && $type->code != "kdana") {
                    if ($contract->status != 'signed' && $contract->user_id == null) {
                        $result = $this->SignContract($id);
                        if ($result['status'] == "false") {
                            return response()->json(['message' => $result['message'], 'error' => $result['error']], 500);
                        }
                    }
                }
            }
        }
        return response()->json(['message' => 'تم التوقيع بنجاح'], 200);
    }

    public function storeByID($assign_id, $company_id)
    {
        try {

            DB::beginTransaction();
            $code = Str::random(30);
            while (Contract::where('qr', $code)->first() != null) {
                $code = Str::random(30);
            }
            $data = array();
            $data['qr'] = $code;
            $contract = Contract::create(array_merge(
                [
                    'assign_camps_id' => $assign_id,
                    'company_id' => $company_id,
                ],
                $data
            ));
            $assign_camp = AssignCamp::find($contract->assign_camps_id);
            if ($assign_camp != null) {
                $assign_camp->update([
                    'contract_status' => 'unsigned'
                ]);
            }
            DB::commit();
            $kdanaUsers = User::join('types', 'types.id', 'users.type_id')->where('code', 'kdana')
                ->select('users.id')->get();
            foreach ($kdanaUsers as $user) {
                $notificationMessage = __('general.newAppointment');
                $link = "/contructs";
                (new NotificationController)->addNotification($user->id, $notificationMessage, $link);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'false', 'error' => $e->getMessage(), 'message' => 'يوجد خطأ يرجى التأكد من البيانات', 'code' => 500];
        }
        return ['status' => 'true', 'message' => 'done', 'code' => 200];
    }
    public function bulkStore(Request $request)
    {
        if ($request->select_all) {
            $AssignCampID = AssignCamp::pluck('id')->toArray();
            foreach ($AssignCampID as $id) {
                $AssignCamp = AssignCamp::find($id);
                $contract = Contract::where('assign_camps_id', $AssignCamp->id)->first();
                if ($contract == null) {
                    $result = $this->storeByID($AssignCamp->id, $AssignCamp->receiver_company_id);
                    if ($result['status'] == "false") {
                        return response()->json(['message' => $result['message'], 'error' => $result['error']], 500);
                    }
                }
            }
        } else {
            foreach ($request->assign_camps_ids as $id) {
                $AssignCamp = AssignCamp::find($id);
                $contract = Contract::where('assign_camps_id', $AssignCamp->id)->first();
                if ($contract == null) {
                    $result = $this->storeByID($AssignCamp->id, $AssignCamp->receiver_company_id);
                    if ($result['status'] == "false") {
                        return response()->json(['message' => $result['message'], 'error' => $result['error']], 500);
                    }
                }
            }
        }

        return response()->json(['message' => 'تم التثبيت بنجاح', 'contract_status' => 'unsigned'], 200);
    }
}

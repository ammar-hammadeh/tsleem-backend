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
                'receiver_cr',
                'receiver_company_id',
                'square.id as square_id',
                'square.name as square_name',
                'camps.id as camp_id',
                'camps.name as camp_name',
                'companies.name as company_name',
                'contracts.status',
                'contracts.id'
            );
        if ($request->start)
            $contract->whereDate('contracts.created_at', '>=', $request->start);
        if ($request->end)
            $contract->whereDate('contracts.created_at', '<=', $request->end);

        if ($request->square)
            $contract->where('square_id', $request->square_id);

        if ($request->camp)
            $contract->where('camp_id', $request->camp_id);

        if ($request->company)
            $contract->where('company_id', $request->company_id);

        if ($request->status)
            $contract->where('status', $request->status);

        $user = Auth::user();
        $user_type = Type::find($user->type_id);
        if ($user_type->code != 'admin' && $user_type->code != 'kdana' && $user_type->code != 'sharer')
            $contract->where('users.company_id', $user->company_id);

        $data = $contract->paginate($paginate);

        return response()->json(['data' => $data, 'filters' => $this->filters()], 200);
    }

    public function store(Request $request)
    {
        $data = array();
        $data['qr'] = Str::random(30);
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
        if ($contract != null) {
            //     $contract = $contract->with('AssignCamps', 'users', 'Ministry', 'Kidana');
            //     if ($contract->company_id != null)
            //         $contract = $contract->with('AssignCamps', 'users', 'Ministry', 'Kidana', 'Company');
            //     elseif ($contract->license != null)
            //         $contract = $contract->with('AssignCamps', 'users', 'Ministry', 'Kidana', 'CompanyLicense');
            return response()->json(['data' => $contract], 200);
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
        else
            $contract->update([
                'user_id' => Auth::user()->id
            ]);

        if ($contract->user_id != null  && $contract->kidana != null) {
            try {
                DB::beginTransaction();
                $contract->update([
                    'status' => 'signed'
                ]);
                if (!$contract->company_id == null)
                    AssignCamp::where('id', $contract->assign_camps_id)->update(
                        [
                            'receiver_company_id' => $contract->company_id,
                            'receiver_cr' => null
                        ]
                    );
                DB::commit();
            } catch (\Exception) {
                DB::rollBack();
                return response()->json(['message' => 'something wrong, please try again later'], 500);
            }
        }
        $assign_camp = AssignCamp::find($contract->assign_camps_id);
        if ($assign_camp != null) {
            $assign_camp->update([
                'contract_status' => 'signed'
            ]);
            return response()->json(['status' => 'signed'], 200);
        } else {
            return response()->json(['message' => 'لايوجد بيانات مطابقة'], 404);
        }
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
        $contract = Contract::where('qr', $qr)->first();
        if ($contract != null)
            return response()->json(['status' => true, 'message' => 'المحضر صالح']);
        else
            return response()->json(['status' => false, 'message' => 'المحضر غير صالح']);
    }
}

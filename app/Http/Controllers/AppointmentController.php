<?php

namespace App\Http\Controllers;

use App\Models\Camp;
use App\Models\Square;
use App\Models\Company;
use App\Models\AssignCamp;
use Illuminate\Http\Request;
use App\Models\UserAppointment;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AppointmentRequest;
use App\Models\Type;
use Illuminate\Database\Eloquent\Collection;

class AppointmentController extends Controller
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
                'label' => __('general.camp'),
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



    public function index(Request $request)
    {

        if ($request->has('paginate'))
            $paginate = $request->paginate;
        else
            $paginate = env('PAGINATE');

        $IDAssignation =  AssignCamp::join('companies', 'companies.id', 'assign_camps.receiver_company_id')
            ->join('camps', 'camps.id', 'assign_camps.camp_id')
            ->join('square', 'square.id', 'assign_camps.square_id')
            ->select(
                'assign_camps.id',
                'assign_camps.created_at',
                'assign_camps.status',
                'receiver_cr',
                'receiver_company_id',
                'square.id as square_id',
                'square.name as square_name',
                'camps.id as camp_id',
                'camps.name as camp_name',
                'companies.name as company_name',
                'contract_status'
            )
            ->where('camps.status', 'ready');

        // $CRAssignation =  AssignCamp::join('companies', 'companies.license', 'assign_camps.receiver_cr')
        //     ->join('camps', 'camps.id', 'assign_camps.camp_id')
        //     ->join('square', 'square.id', 'assign_camps.square_id')
        //     ->select(
        //         'assign_camps.id',
        //         'assign_camps.created_at',
        //         'assign_camps.status',
        //         'receiver_cr',
        //         'receiver_company_id',
        //         'square.id as square_id',
        //         'square.name as square_name',
        //         'camps.id as camp_id',
        //         'camps.name as camp_name',
        //         'companies.name as company_name',
        //         'contract_status'
        //     )
        //     ->where('camps.status', 'ready');
        //check user type
        if (Auth::guard('api')->check()) {
            $userTypeID = Auth::user()->type_id;
            $userType = Type::where('id', $userTypeID)->value('code');
            $userCompany = Company::where('owner_id', Auth::user()->id)->value('license');
            if ($userType != 'admin') {
                // $CRAssignation->where('assign_camps.assigner_cr', $userCompany);
                $IDAssignation->where('assign_camps.assigner_cr', $userCompany);
            }
        }

        //filters
        if ($request->start != '') {
            // $CRAssignation->whereDate('created_at', '>=', $request->start);
            $IDAssignation->whereDate('created_at', '>=', $request->start);
        }
        if ($request->end != '') {
            // $CRAssignation->whereDate('created_at', '<=', $request->end);
            $IDAssignation->whereDate('created_at', '<=', $request->end);
        }
        if ($request->status != '') {
            // $CRAssignation->where('status', $request->status);
            $IDAssignation->where('status', $request->status);
        }
        // if ($request->deliver_status != '') {
        //     $CRAssignation->where('deliver_status', $request->deliver_status);
        //     $IDAssignation->where('deliver_status', $request->deliver_status);
        // }

        if ($request->receiver_cr != '') {
            // $CRAssignation->where('receiver_cr', $request->receiver_cr);
            $IDAssignation->where('receiver_cr', $request->receiver_cr);
        }

        if ($request->receiver_company_id != '') {
            // $CRAssignation->where('receiver_company_id', $request->receiver_company_id);
            $IDAssignation->where('receiver_company_id', $request->receiver_company_id);
        }

        if ($request->square != '') {
            // $CRAssignation->where('square_id', $request->square_id);
            $IDAssignation->where('square_id', $request->square_id);
        }

        if ($request->camp != '') {
            // $CRAssignation->where('camp_id', $request->camp_id);
            $IDAssignation->where('camp_id', $request->camp_id);
        }

        // $CRAssignation = $CRAssignation->paginate($paginate);
        // $IDAssignation = $IDAssignation->paginate($paginate);

        // $CRAssignation->merge($IDAssignation);
        // $CRAssignation = $CRAssignation->merge($IDAssignation);

        $result = $IDAssignation->where('assign_camps.status','!=','appointment')->paginate($paginate);

        return response()->json(['message' => 'assignations got successfully', 'data' => $result]);
    }


    public function appointments(Request $request)
    {

        if ($request->has('paginate'))
            $paginate = $request->paginate;
        else
            $paginate = env('PAGINATE');

        $IDAppointments =  UserAppointment::join('assign_camps', 'assign_camps.id', 'users_appointments.assign_camp_id')
            ->join('companies', 'companies.license', 'assign_camps.receiver_cr')
            ->join('camps', 'camps.id', 'assign_camps.camp_id')
            ->join('square', 'square.id', 'assign_camps.square_id')
            ->select(
                'assign_camps.id',
                'assign_camps.created_at',
                'assign_camps.status',
                'receiver_cr',
                'receiver_company_id',
                'square.id as square_id',
                'square.name as square_name',
                'camps.id as camp_id',
                'camps.name as camp_name',
                'appointment',
                'appointment_status',
                'companies.name as company_name'
            );
        // 'companies.name as company_name',
        // $CRAppointments =  UserAppointment::join('assign_camps', 'assign_camps.id', 'users_appointments.assign_camp_id')
        //     ->join('companies', 'companies.id', 'assign_camps.receiver_company_id')
        //     ->join('camps', 'camps.id', 'assign_camps.camp_id')
        //     ->join('square', 'square.id', 'assign_camps.square_id')
        //     ->select(
        //         'assign_camps.id',
        //         'assign_camps.created_at',
        //         'assign_camps.status',
        //         'receiver_cr',
        //         'receiver_company_id',
        //         'square.id as square_id',
        //         'square.name as square_name',
        //         'camps.id as camp_id',
        //         'camps.name as camp_name',
        //         'appointment',
        //         'appointment_status',
        //         'companies.name as company_name'
        //     );

        //filters
        if ($request->start != '') {
            // $CRAppointments->whereDate('appointment', '>=', $request->start);
            $IDAppointments->whereDate('appointment', '>=', $request->start);
        }
        if ($request->end != '') {
            // $CRAppointments->whereDate('appointment', '<=', $request->end);
            $IDAppointments->whereDate('appointment', '<=', $request->end);
        }
        if ($request->appointment_status != '') {
            // $CRAppointments->where('appointment_status', $request->appointment_status);
            $IDAppointments->where('appointment_status', $request->appointment_status);
        }
        if ($request->deliver_status != '') {
            // $CRAppointments->where('deliver_status', $request->deliver_status);
            $IDAppointments->where('deliver_status', $request->deliver_status);
        }
        if ($request->receiver_cr != '') {
            // $CRAppointments->where('receiver_cr', $request->receiver_cr);
            $IDAppointments->where('receiver_cr', $request->receiver_cr);
        }
        if ($request->receiver_company_id != '') {
            // $CRAppointments->where('receiver_company_id', $request->receiver_company_id);
            $IDAppointments->where('receiver_company_id', $request->receiver_company_id);
        }
        if ($request->square != '') {
            // $CRAppointments->where('square_id', $request->square_id);
            $IDAppointments->where('square_id', $request->square_id);
        }
        if ($request->camp != '') {
            // $CRAppointments->where('camp_id', $request->camp_id);
            $IDAppointments->where('camp_id', $request->camp_id);
        }


        // $query1 =  $CRAppointments->get();
        // $query2 = $IDAppointments->get();

        // $result = $query1->merge($query2);
        $result = $IDAppointments->paginate($paginate);

        return response()->json(['message' => 'appointments got successfully', 'data' => $result]);
    }



    public function store(AppointmentRequest $request)
    {
        $assignation = AssignCamp::find($request->assign_camp_id);
        if (!$assignation)
            return response()->json('please check assignaction id and try again', 500);

        $oldAppointment = UserAppointment::where('assign_camp_id', $request->assign_camp_id)
            ->where('appointment_status', 'pending')->first();
        if ($oldAppointment)
            return response()->json('You\'ve already created an appointment for this camp', 500);

        $oldAppointment = UserAppointment::where('assign_camp_id', $request->assign_camp_id)
            ->where('deliver_status', 'approved')->first();
        if ($oldAppointment)
            return response()->json(['message' => 'You\'ve already delivered this camp'], 500);

        UserAppointment::create($request->all());
        $messageForApplicant = '';
        return response()->json(['message' => 'Appointment created successfully']);
    }

    public function update($id, AppointmentRequest $request)
    {
        $appointment = UserAppointment::find($id);
        if (!$appointment)
            return response()->json(['message' => 'please check appointment id and try again'], 500);

        $assignation = AssignCamp::find($request->assign_camp_id);
        if (!$assignation)
            return response()->json(['message' => 'please check assignation id and try again'], 500);

        $appointment->update(
            [
                'assign_camp_id' => $request->assign_camp_id,
                'appointment' => $request->appointment
            ]
        );
        return response()->json(['message' => 'Appointment updated successfully']);
    }


    public function delete($id)
    {
        $appointment = UserAppointment::find($id);
        if (!$appointment)
            return response()->json(['message' => 'please check appointment id and try again'], 500);

        $appointment->delete();
        return response()->json(['message' => 'Appointment deleted successfully']);
    }
}

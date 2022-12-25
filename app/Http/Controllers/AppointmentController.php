<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Camp;
use App\Models\Type;
use App\Models\Square;
use App\Models\Company;
use App\Helper\LogHelper;
use App\Jobs\SendEmailJob;
use App\Models\AssignCamp;
use Illuminate\Http\Request;
use App\Models\UserAppointment;
use Modules\Core\Entities\User;
use Modules\Core\Mail\SendEmail;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AppointmentRequest;

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
            // [
            //     'name' => 'receiver_company_id',
            //     'value' => '',
            //     'label' => __('general.CR Number'),
            //     'type' => 'auto-complete',
            //     'items' => Company::whereHas('Type', function ($query) {
            //         $query->whereIn('code', ['raft_company', 'service_provider', 'raft_office']);
            //     })->get(),
            //     'itemText' => 'name',
            //     'itemValue' => 'id'
            // ],
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
                'name' => 'company',
                'value' => '',
                'label' => __('general.Company'),
                'type' => 'auto-complete',
                'items' => Company::whereHas('Type', function ($query) {
                    $query->whereIn('code', ['raft_company', 'service_provider', 'raft_office']);
                })->get(),
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
                    ['name' => 'answered', 'label' => __('general.answered')],
                    ['name' => 'deliverd', 'label' => __('general.Deliverd')]
                ],
                'itemText' => 'label',
                'itemValue' => 'name'
            ],

        ];
        return $filters;
    }


    public function filter_spacific_appointment()
    {
        $filters = [
            [
                'name' => 'type_id',
                'value' => '',
                'label' => __('general.user type'),
                'type' => 'select',
                'items' => Type::whereNull('deleted_at')->get(),
                'itemText' => 'name',
                'itemValue' => 'id'
            ],
            [
                'name' => 'company',
                'value' => '',
                'label' => __('general.Company'),
                'type' => 'auto-complete',
                'items' => Company::whereHas('Type', function ($query) {
                    $query->whereIn('code', ['raft_company', 'service_provider', 'raft_office']);
                })->get(),
                'itemText' => 'name',
                'itemValue' => 'id'
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
            ->join('types', 'types.id', 'companies.type_id')
            ->join('camps', 'camps.id', 'assign_camps.camp_id')
            ->join('square', 'square.id', 'assign_camps.square_id')
            ->select(
                'assign_camps.id',
                'assign_camps.created_at',
                'assign_camps.status',
                'receiver_company_id',
                'square.id as square_id',
                'square.name as square_name',
                'camps.id as camp_id',
                'camps.name as camp_name',
                'companies.name as company_name',
                'contract_status',
            )
            ->where('camps.status', 'ready')
            ->whereNotIn('assign_camps.status', ['appointment', 'deliverd', 'answered']);

        //filters
        if ($request->start != '')
            $IDAssignation->whereDate('created_at', '>=', $request->start);
        if ($request->end != '')
            $IDAssignation->whereDate('created_at', '<=', $request->end);
        if ($request->status != '')
            $IDAssignation->where('status', $request->status);
        if ($request->receiver_company_id != '')
            $IDAssignation->where('receiver_company_id', $request->receiver_company_id);
        if ($request->square != '')
            $IDAssignation->where('square_id', $request->square);
        if ($request->camp != '')
            $IDAssignation->where('camps.id', $request->camp);
        if ($request->type_id != '')
            $IDAssignation->where('types.id', $request->type_id);
        if ($request->company != '')
            $IDAssignation->where('companies.id', $request->company);

        //check user type
        if (Auth::guard('api')->check()) {
            $userType = Type::where('id', Auth::user()->type_id)->value('code');
            if ($userType != 'admin' && $userType != 'raft_company'  && $userType != 'sharer' && $userType != 'consulting_office' && $userType != 'contractor') {
                $IDAssignation->where('receiver_company_id', Auth::user()->company_id);
            } elseif ($userType == 'raft_company') {
                $IDAssignation->where('assigner_company_id', Auth::user()->company_id);
            }
        }

        $result = $IDAssignation->paginate($paginate);

        return response()->json(['message' => 'assignations got successfully', 'filters' => $this->filter_spacific_appointment(), 'data' => $result]);
    }


    public function appointments(Request $request)
    {

        if ($request->has('paginate'))
            $paginate = $request->paginate;
        else
            $paginate = env('PAGINATE');
        $IDAppointments =  UserAppointment::join('assign_camps', 'assign_camps.id', 'users_appointments.assign_camp_id')
            ->join('companies', 'companies.id', 'assign_camps.receiver_company_id')
            ->join('camps', 'camps.id', 'assign_camps.camp_id')
            ->join('square', 'square.id', 'assign_camps.square_id')
            ->selectRaw(
                'users_appointments.assign_camp_id,
                users_appointments.id,
                assign_camps.created_at,
                assign_camps.status,
                receiver_company_id,
                square.id as square_id,
                square.name as square_name,
                camps.id as camp_id,
                camps.name as camp_name,
                appointment,
                appointment_status,
                companies.name as company_name,
                companies.id as company_id,
                date(appointment) as appointment_date'
            );

        //filters
        if ($request->start != '')
            $IDAppointments->whereDate('appointment', '>=', $request->start);
        if ($request->end != '')
            $IDAppointments->whereDate('appointment', '<=', $request->end);
        if ($request->appointment_status != '')
            $IDAppointments->where('appointment_status', $request->appointment_status);
        if ($request->deliver_status != '')
            $IDAppointments->where('deliver_status', $request->deliver_status);
        if ($request->receiver_company_id != '')
            $IDAppointments->where('receiver_company_id', $request->receiver_company_id);
        if ($request->square != '')
            $IDAppointments->where('square.id', $request->square);
        if ($request->camp != '')
            $IDAppointments->where('camps.id', $request->camp);
        if ($request->company != '')
            $IDAppointments->where('companies.id', $request->company);

        if ($request->status) {
            $IDAppointments->where('assign_camps.status', $request->status);

            if ($request->start != '')
                $IDAppointments->whereDate('appointment', '>=', $request->start);
        }
        // }else{
        //     $IDAppointments->where('assign_camps.status','!=', 'answered')->where('assign_camps.status','!=', 'deliverd');
        // }

        //check user type
        if (Auth::guard('api')->check()) {
            $userType = Type::where('id', Auth::user()->type_id)->value('code');
            if ($userType != 'admin' && $userType != 'raft_company' && $userType != 'kdana' && $userType != 'sharer' && $userType != 'consulting_office' && $userType != 'contractor') {
                $IDAppointments->where('receiver_company_id', Auth::user()->company_id);
            } elseif ($userType == 'raft_company') {
                $IDAppointments->where('assigner_company_id', Auth::user()->company_id);
            }
        }


        $result = $IDAppointments->paginate($paginate);

        return response()->json(['message' => 'appointments got successfully', 'filters' => $this->filters(), 'data' => $result]);
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

        $user_appointment = UserAppointment::create($request->all());

        $appointment = UserAppointment::with(
            'getAssignCamps.getCompany',
            'getAssignCamps.getCamp',
            'getAssignCamps.getSquare'
        )->find($user_appointment->id);
        // appointment

        $user_id = Auth::user()->id;
        $old_value = null;
        $new_value = [
            'company' => $appointment->getAssignCamps->getCompany->name,
            'camp' => $appointment->getAssignCamps->getCamp->name,
            'square' => $appointment->getAssignCamps->getSquare->name,
            'appointment' => $appointment->appointment,
        ];
        $module = 'appointment';
        $method_id = 1;
        $message = __('logTr.addAppointment');

        LogHelper::storeLog(
            $user_id,
            json_decode(json_encode($old_value)),
            json_decode(json_encode($new_value)),
            $module,
            $method_id,
            $message,
        );


        $assignation->update([
            'status' => 'appointment'
        ]);
        $user_id = Company::where('id', $assignation->receiver_company_id)->value('owner_id');
        $user = User::find($user_id);
        $camp = Camp::find($assignation->camp_id);
        $appoint = Carbon::parse($user_appointment->appointment)->toDateTimeString();
        sendSMS($user->phone, "تم تحديد موعد تسليم المخيم ( $camp->name ) في ( $appoint )");
        $Emaildata = array(
            "name" => $user->name,
            "body" => "تم تحديد موعد تسليم المخيم ( $camp->name ) في ( $appoint )",
            "subject" => "تحديد موعد تسليم" . env('APP_NAME')
        );
        dispatch(new SendEmailJob($user->email, new SendEmail($Emaildata, "Notification")))->onConnection('database');

        return response()->json(['message' => 'Appointment created successfully']);
    }

    public function update($id, AppointmentRequest $request)
    {
        $appointment = UserAppointment::find($id);
        if ($appointment == null)
            return response()->json(['message' => 'please check appointment id and try again'], 500);

        $assignation = AssignCamp::find($request->assign_camp_id);
        if ($assignation == null)
            return response()->json(['message' => 'please check assignation id and try again'], 500);

        $old = UserAppointment::with(
            'getAssignCamps.getCompany',
            'getAssignCamps.getCamp',
            'getAssignCamps.getSquare'
        )->find($assignation->id);
        // appointment
        $user_id = Auth::user()->id;
        $old_value = [
            'company' => $old->getAssignCamps->getCompany->name,
            'camp' => $old->getAssignCamps->getCamp->name,
            'square' => $old->getAssignCamps->getSquare->name,
            'appointment' => $old->appointment,
        ];
        $appointment->update(
            [
                'assign_camp_id' => $request->assign_camp_id,
                'appointment' => $request->appointment
            ]
        );
        $new = UserAppointment::with(
            'getAssignCamps.getCompany',
            'getAssignCamps.getCamp',
            'getAssignCamps.getSquare'
        )->find($assignation->id);
        $new_value = [
            'company' => $new->getAssignCamps->getCompany->name,
            'camp' => $new->getAssignCamps->getCamp->name,
            'square' => $new->getAssignCamps->getSquare->name,
            'appointment' => $new->appointment,
        ];
        $module = 'appointment';
        $method_id = 2;
        $message = __('logTr.updateAppointment');

        LogHelper::storeLog(
            $user_id,
            json_decode(json_encode($old_value)),
            json_decode(json_encode($new_value)),
            $module,
            $method_id,
            $message,
        );
        return response()->json(['message' => 'Appointment updated successfully', 'data' => $request->appointment]);
    }


    public function delete($id)
    {
        $appointment = UserAppointment::find($id);
        if (!$appointment)
            return response()->json(['message' => 'please check appointment id and try again'], 500);

        $old = UserAppointment::with(
            'getAssignCamps.getCompany',
            'getAssignCamps.getCamp',
            'getAssignCamps.getSquare'
        )->find($appointment->id);
        // appointment

        $user_id = Auth::user()->id;
        $old_value = null;
        $new_value = [
            'company' => $old->getAssignCamps->getCompany->name,
            'camp' => $old->getAssignCamps->getCamp->name,
            'square' => $old->getAssignCamps->getSquare->name,
            'appointment' => $old->appointment,
        ];
        $module = 'appointment';
        $method_id = 3;
        $message = __('logTr.deleteAppointment');

        LogHelper::storeLog(
            $user_id,
            json_decode(json_encode($old_value)),
            json_decode(json_encode($new_value)),
            $module,
            $method_id,
            $message,
        );
        $appointment->delete();
        return response()->json(['message' => 'Appointment deleted successfully']);
    }
}

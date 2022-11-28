<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentRequest;
use App\Models\AssignCamp;
use App\Models\Camp;
use App\Models\Company;
use App\Models\Square;
use App\Models\UserAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Modules\Core\Entities\User;
use Modules\Core\Mail\SendEmail;

class GeneralController extends Controller
{
    //
    public function DashboardCounter()
    {
        $total_user = User::count();
        $total_company = Company::count();
        $total_camps = Camp::count();
        $total_square = Square::count();
        $pending_user = User::where('status', 'pending')->count();
        $ready_camps = Camp::where('status', 'ready')->count();
        $user = User::with('Type')->find(Auth::user()->id);


        //new cards
        $assignations = AssignCamp::count();
        $appointments = UserAppointment::count();

        //Charts 
        //     $assignations_chart = AssignCamp::selectRaw(
        //         "
        // count(case when status = 'pending' then 1 end ) as Pending,
        // count(case when status = 'returned' then 1 end ) as Returned,
        // count(case when status = 'appointment' then 1 end ) as Appointment,
        // count(case when status = 'answered' then 1 end ) as Answered,
        // count(case when status = 'deliverd' then 1 end ) as Deliverd"
        //     )
        //         ->groupBy('status')
        //         ->get();

        $assignations_chart_data = AssignCamp::selectRaw('status as lables,count(*) as count')
            ->groupBy('status')
            ->get();

        $assignations_chart['lables'] = $assignations_chart_data->pluck('lables');
        $assignations_chart['count'] = $assignations_chart_data->pluck('count');

        $appointments_chart_data = UserAppointment::selectRaw('deliver_status as lables,count(*) as count')
            ->groupBy('deliver_status')
            ->get();

        $appointments_chart['lables'] = $appointments_chart_data->pluck('lables');
        $appointments_chart['count'] = $appointments_chart_data->pluck('count');


        $camps_chart_data = Camp::selectRaw('status as lables,count(*) as count')
            ->groupBy('status')
            ->get();

        $camps_chart['lables'] = $camps_chart_data->pluck('lables');
        $camps_chart['count'] = $camps_chart_data->pluck('count');

        $data = [
            /////Cards
            'total_user' => $total_user,
            'total_company' => $total_company,
            'total_camps' => $total_camps,
            'total_square' => $total_square,
            'pending_user' => $pending_user,
            'ready_camps' => $ready_camps,

            /////New
            //card
            'assignations' => $assignations, // عدد التخصيصات الكلي
            'appointments' => $appointments, // عدد الحجوزات الكلي
            //Carts
            'assignations_chart' => $assignations_chart, // التخصيصات بحسب الحالة 
            'appointments_chart' => $appointments_chart, // الحجوزات بحسب حالة التسليم 
            'camps_chart' => $camps_chart, // المخيمات بحسب الحالة
        ];
        return response()->json(['user' => $user, 'data' => $data], 200);
    }

    public function TestSMS()
    {
        sendSMS();
    }
    public function OTPSMS()
    {
        sendOTPSMS();
    }

    public function VerfiySMS()
    {
        verfiyOTPSMS();
    }

    // testing

    public function TestEmail()
    {
        $data = array(
            "name" => 'Ammar',
            "subject" => "Get Started, Welcome in " . env('APP_NAME')
        );
        
        Mail::to('ammar.hammadeh94@gmail.com')->send(new SendEmail($data, "NewAccount"));
    }
}

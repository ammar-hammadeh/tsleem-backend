<?php

use App\Models\Admin;
use Modules\Core\Entities\User;
use App\Models\CustomerInquiries;
use App\Models\RepeatableInquiry;
use App\Models\RegisterFormAnswer;
use Illuminate\Support\Facades\DB;
use App\Models\RegisterFormInquiry;
use App\Http\Controllers\NotificationController;

function check_status_required_question($id)
{
    $questions_required = RegisterFormInquiry::where('is_required', 1)->pluck('inquiry_id')->toArray();
    $register_form = RegisterFormAnswer::where('user_id', $id)
        ->whereIn('inquiry_id', $questions_required)
        ->count();
    if ($register_form == count($questions_required)) {
        return  0; // doesn't have
    } else {
        return  1; // have
    }
}

function get_admins_for_notification()
{
    //admins id for sent notification
    $admins_ids = Admin::pluck('id')->toArray();
    return User::whereIn('id', $admins_ids)->get();
}

function set_inquery_global($user_id)
{
    $inquiries = CustomerInquiries::where('global', 1)->distinct()->pluck('inquery_id')->toArray();

    // dd($inquiries);
    for ($i = 0; $i < count($inquiries); $i++) {
        $data = [
            'inquery_id' => $inquiries[$i],
            'customer_id' => $user_id,
            'global' => 1
        ];
        CustomerInquiries::create($data);

        for ($d = 2018; $d <= date("Y"); $d++) {
            for ($x = 1; $x <= 12; $x++) {
                RepeatableInquiry::create([
                    'user_id' => $user_id,
                    'inquiry_id' => $inquiries[$i],
                    'year' => $d,
                    'month' => $x,
                ]);
                if ($i == date('Y') && $x == date('m')) {
                    break;
                }
            }
        }
    }
}

function send_notification_admin_group($notificationMessage, $link, $ticket_subject)
{
    $admin_groups = DB::table('ticket_admin_group_relation')->where('ticket_admin_group_id', $ticket_subject->ticket_admin_group_id)->get();
    foreach ($admin_groups as $admin) {
        if ($admin->admin_id != $ticket_subject->admin_id) (new NotificationController)->addNotification($admin->admin_id, $notificationMessage, $link);
    }
    return true;
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\NotificationRequest;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function userNotification(Request $request)
    {
        $notifications = Notification::where('user_id', Auth::guard('api')->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(env('PAGINATE_NPTIFICATION'));
        $data['notifications'] = $notifications;
        if ($request->first) {
            $unread_notify = Notification::where('user_id', Auth::guard('api')->user()->id)
                ->where('is_seen', 0)->count();
            $data['unread_notify'] = $unread_notify;
        }
        return response()->json($data);
    }

    public function makeNotificationSeen($id)
    {
        $notification = Notification::find($id);
        if ($notification) {
            $notification->update(['is_seen' => 1]);
        }
        return true;
    }
    public function makeAllNotificationSeen()
    {
        $notification = Notification::where('user_id', Auth::guard('api')->user()->id)->where('is_seen', 0)->update(['is_seen' => 1]);
        // foreach ($notification as $notif)
        //     $notif->update(['is_seen' => 1]);
        return true;
    }

    public function addNotification($user_id, $message, $link)
    {
        Notification::create([
            'user_id' => $user_id,
            'message' => $message,
            'link' => $link
        ]);
    }
}

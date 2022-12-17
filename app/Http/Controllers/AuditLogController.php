<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\LogAction;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
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
                'name' => 'log_name',
                'value' => '',
                'label' => __('general.Name'),
                'type' => 'text',
                'items' => ''
            ],
            [
                'name' => 'user_id',
                'value' => '',
                'label' => __('general.user'),
                'type' => 'auto-complete',
                'items' => User::get(),
                'itemText' => 'name',
                'itemValue' => 'id'
            ],
        ];
        return $filters;
    }


    public function index(Request $request)
    {

        $paginate = env('PAGINATE');
        if ($request->has('paginate')) {
            $paginate = $request->paginate;
        }

        //     "id": 3,
        //     "log_name": "default",
        //     "description": "you have created a record in the table square",
        //     "subject_type": "App\\Models\\Square",
        //     "event": "created",
        //     "subject_id": 152,
        //     "causer_type": "Modules\\Core\\Entities\\User",
        //     "causer_id": 1,
        //     "properties": "{\"attributes\":{\"name\":\"temp5\"}}",
        //     "batch_uuid": null,
        //     "created_at": "2022-12-10T21:02:04.000000Z",

        // $query = AuditLog::with('getActions');
        $query = ActivityLog::join('users', 'users.id', 'activity_log.causer_id')
            ->select('log_name', 'event', 'name', 'properties', 'activity_log.created_at');

        if ($request->start != '')
            $query->whereDate('created_at', '>=', $request->start);
        if ($request->end != '')
            $query->whereDate('created_at', '<=', $request->end);
        if ($request->log_name != '')
            $query->where('log_name', 'like', '%' . $request->log_name . '%');
        if ($request->user_id != '')
            $query->where('causer_id', $request->user_id);

        $logs = $query->paginate($paginate);
        return response()->json([
            "data" => $logs,
            'filters' => $this->filters()
        ], 200);
    }
}

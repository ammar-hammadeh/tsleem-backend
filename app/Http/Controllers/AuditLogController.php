<?php

namespace App\Http\Controllers;

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
                'name' => 'action_id',
                'value' => '',
                'label' => __('general.actionType'),
                'type' => 'auto-complete',
                'items' => LogAction::get(),
                'itemText' => 'name',
                'itemValue' => 'id'
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

        $query = AuditLog::with('getActions');

        if ($request->start != '')
            $query->whereDate('created_at', '>=', $request->start);
        if ($request->end != '')
            $query->whereDate('created_at', '<=', $request->end);
        if ($request->action_id != '')
            $query->where('action_id', $request->action_id);
        if ($request->user_id != '')
            $query->where('user_id', $request->user_id);

        $logs = $query->paginate($paginate);
        return response()->json(["data" => $logs, 'filters' => $this->filters()], 200);
    }
}

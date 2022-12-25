<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;
use Modules\Core\Entities\User;
use Illuminate\Support\Collection;

class SystemLogController extends Controller
{
    public function filters()
    {
        $filters = [
            [
                'name' => 'start',
                'value' => '',
                'label' => __('general.Start'),
                'type' => 'datee',
                'items' => ''
            ],
            [
                'name' => 'end',
                'value' => '',
                'label' => __('general.End'),
                'type' => 'datee',
                'items' => ''
            ],
            [
                'name' => 'value',
                'value' => '',
                'label' => __('logTr.value'),
                'type' => 'text',
                'items' => ''
            ],            [
                'name' => 'message',
                'value' => '',
                'label' => __('logTr.message'),
                'type' => 'text',
                'items' => ''
            ],
            [
                'name' => 'module',
                'value' => '',
                'label' => __('logTr.module'),
                'type' => 'auto-complete',
                'items' => collect(
                    [
                        ['id' => 'square', 'name' => __('logTr.square'),],
                        ['id' => 'camps', 'name' => __('logTr.camps')],
                        ['id' => 'users', 'name' => __('logTr.users')],
                        ['id' => 'roles', 'name' => __('logTr.roles')],
                        ['id' => 'assignCamps', 'name' => __('logTr.assignCamps')],
                        ['id' => 'question', 'name' => __('logTr.question')],
                        ['id' => 'questionCategory', 'name' => __('logTr.questionCategory')],
                        ['id' => 'formTemplate', 'name' => __('logTr.formTemplate')],
                        ['id' => 'appointment', 'name' => __('logTr.appointment')],
                    ]
                ),
                'itemText' => 'name',
                'itemValue' => 'id'
            ],
            [
                'name' => 'method_id',
                'value' => '',
                'label' => __('logTr.method'),
                'type' => 'auto-complete',
                'items' => collect(
                    [
                        ['id' => 1, 'name' => __('logTr.insert')],
                        ['id' => 2, 'name' => __('logTr.update')],
                        ['id' => 3, 'name' => __('logTr.delete')],
                        ['id' => 4, 'name' => __('logTr.delete')],
                        ['id' => 5, 'name' => __('logTr.ResetPassword')],
                        ['id' => 6, 'name' => __('logTr.Register')],
                        ['id' => 7, 'name' => __('logTr.Login')],
                    ]
                ),
                'itemText' => 'name',
                'itemValue' => 'id'
            ],            [
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

        $query = SystemLog::join('users', 'users.id', 'system_logs.user_id')
            ->select(
                'users.name',
                'old_value',
                'new_value',
                'module',
                'message',
                'method_id',
                'system_logs.created_at'
            );

        if ($request->start != '')
            $query->whereDate('system_logs.created_at', '>=', $request->start);
        if ($request->end != '')
            $query->whereDate('system_logs.created_at', '<=', $request->end);
        if ($request->module != '')
            $query->where('module', $request->module);
        if ($request->method_id != '')
            $query->where('method_id', $request->method_id);
        if ($request->user_id != '')
            $query->where('user_id', $request->user_id);
        if ($request->message != '')
            $query->where('message', 'like', '%' . $request->message . '%');
        if ($request->value != '') {
            $query->where('old_value', 'like', '%' . $request->value . '%');
            $query->orWhere('new_value', 'like', '%' . $request->value . '%');
        }

        $logs = $query->paginate($paginate);
        return response()->json([
            "data" => $logs,
            'filters' => $this->filters()
        ], 200);
    }
}

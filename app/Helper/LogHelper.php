<?php

namespace App\Helper;

use App\Models\AuditLog;

class LogHelper
{
    public static function storeLog(Int $user_id, Int $action_id, String $note)
    {
        AuditLog::create([
            'user_id' => $user_id,
            'action_id' => $action_id,
            'note' => $note,
        ]);
        return true;
    }
}

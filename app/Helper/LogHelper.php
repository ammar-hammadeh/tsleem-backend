<?php

namespace App\Helper;

use App\Models\AuditLog;
use App\Models\SystemLog;

class LogHelper
{
    public static function storeLog(Int $user_id, $old_value, $new_value, String $module, Int $method_id, $message)
    {
        try {
            SystemLog::create(
                [
                    'user_id' => $user_id,
                    'old_value' => $old_value,
                    'new_value' => $new_value,
                    'module' => $module,
                    'method_id' => $method_id,
                    'message' => $message,
                ]
            );
            return true;
        } catch (\Exception $e) {
            return true;
        }
    }
}

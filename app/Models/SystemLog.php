<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'old_value',
        'new_value',
        'module',
        'method_id',
        'message',
    ];

    public function getUser()
    {
        return $this->hasOne(User::class, 'user_id', 'id');
    }

    protected $casts = [
        'old_value' => 'collection',
        'new_value' => 'collection',
    ];


    protected $appends = ['method', 'module_text'];
    public function getModuleTextAttribute($val)
    {
        return __('logTr.' . $this->module);
    }
    public function getMethodAttribute($val)
    {
        switch ($this->method_id) {
            case (1):
                return __('logTr.insert');
                break;

            case (2):
                return __('logTr.update');
                break;

            case (3):
                return __('logTr.delete');

            case (5):
                return __('logTr.ResetPassword');
            case (6):
                return __('logTr.Register');
            case (7):
                return __('logTr.Login');

                // default:
                //     return __('logTr.update');
        }
    }
}

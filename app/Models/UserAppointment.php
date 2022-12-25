<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAppointment extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'users_appointments';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'assign_camp_id',
                'appointment_status',
                'deliver_status',
                'appointment'
            ])->useLogName('appointments');
    }
    public function getAssignCamps()
    {
        return $this->belongsTo(AssignCamp::class, 'assign_camp_id', 'id');
    }
}

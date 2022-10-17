<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAppointment extends Model
{
    use HasFactory;
    protected $fillable = ['assign_camp_id', 'appointment'];
    protected $table = 'users_appointments';


    public function getAssignCamps()
    {
        return $this->belongsTo(AssignCamp::class, 'assign_camp_id', 'id');
    }
}

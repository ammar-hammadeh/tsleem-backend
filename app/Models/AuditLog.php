<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;
    protected $fillable = ['action_id', 'user_id'];

    public function getActions()
    {
        return $this->hasOne(LogAction::class, 'action_id', 'id');
    }
}

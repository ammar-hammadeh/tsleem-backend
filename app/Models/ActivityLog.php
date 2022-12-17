<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;
    protected $table = 'activity_log';
    protected $casts = [
        'properties' => 'collection',
    ];
    protected $appends = [
        'event_text'
    ];

    public function getEventTextAttribute($val)
    {
        return __('general.' . $this->event);
    }
}

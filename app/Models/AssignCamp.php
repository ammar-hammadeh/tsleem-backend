<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;

class AssignCamp extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'assign_camps';
    protected $guarded = [];

    protected $appends = [
        'status_text'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'assigner_cr',
                'assigner_company_id',
                'receiver_company_id',
                'square_id',
                'camp_id',
            ])->useLogName('Assignation');
    }

    public function getStatusTextAttribute($val)
    {
        return __('general.' . $this->status);
    }
    public function getSquare()
    {
        return $this->belongsTo(Square::class, 'square_id', 'id');
    }

    public function getCamp()
    {
        return $this->belongsTo(Camp::class, 'camp_id', 'id');
    }

    public function getCompany()
    {
        return $this->belongsTo(Company::class, 'receiver_company_id', 'id');
    }
}

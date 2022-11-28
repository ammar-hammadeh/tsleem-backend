<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignCamp extends Model
{
    use HasFactory;
    protected $table = 'assign_camps';
    protected $guarded = [];

    protected $appends = [
        'status_text'
    ];

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Square;

class Camp extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'name',
        'square_id',
        'delever_date',
        'status'
    ];

    public function square()
    {
        return $this->belongsTo(Square::class);
    }

    protected $appends = [
        'status_text'
    ];

    public function getStatusTextAttribute($val)
    {
        return __('general.' . $this->status);
    }
}

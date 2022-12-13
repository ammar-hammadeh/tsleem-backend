<?php

namespace App\Models;

use App\Models\Square;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Camp extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable =
    [
        'name',
        'square_id',
        'delever_date',
        'status',
        'developed_name',
        'gate',
        'street',
        'location_id',
        'is_developed'
    ];

    // protected static $logName = 'Camps';
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('Camps');
    }

    public function square()
    {
        return $this->belongsTo(Square::class);
    }

    protected $appends = [
        'status_text',
        'is_developed_text'
    ];

    public function getStatusTextAttribute($val)
    {
        return __('general.' . $this->status);
    }
    public function getIsDevelopedTextAttribute($val)
    {
        return $this->is_developed ? __('general.yes') : __('general.no');
    }
    public function Location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    public function Establishment_plots()
    {
        return $this->belongsTo(Establishment_plots_lookup::class, 'est_plot_lookup_id');
    }
}

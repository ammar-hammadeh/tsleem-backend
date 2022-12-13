<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectricalMeter extends Model
{
    use HasFactory;
    protected $table = 'electrical_meters';
    protected $guarded = [];
    protected $appends = [
        'shared_text',
        'metric_status_text',
        'payment_status_text',
    ];

    public function getMetricStatusTextAttribute($val)
    {
        return $this->metric_status == 1 ? __('general.Active_metric') : __('general.disabled_metric');
    }

    public function getPaymentStatusTextAttribute($val)
    {
        return $this->payment_status == 1 ? __('general.payment') : __('general.unpayment');
    }

    public function getSharedTextAttribute($val)
    {
        return $this->shared_status == 1 ? __('general.yes') : __('general.no');
    }

    public function Camp()
    {
        return $this->belongsToMany(Camp::class, 'camp_electrical_meter_lookup', 'electrical_meter_id', 'camp_id');
    }
    // public function Location()
    // {
    //     return $this->belongsTo(Location::class, 'location_id');
    // }

}

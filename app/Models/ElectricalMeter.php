<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectricalMeter extends Model
{
    use HasFactory;
    protected $table = 'electrical_meters';
    protected $guarded = [];

    public function Camp()
    {
        return $this->belongsToMany(Camp::class,'camp_electrical_meter_lookup','electrical_meter_id','camp_id');
    }
    public function Location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

}

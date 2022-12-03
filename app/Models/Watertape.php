<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Watertape extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'water_tapes';

    public function Location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function Camp()
    {
        return $this->belongsToMany(Camp::class,'camp_water_tapes_lookup','water_tape_id','camp_id');
    }


}

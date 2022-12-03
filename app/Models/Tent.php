<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tent extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'tents';

    public function Location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    public function Camp()
    {
        return $this->belongsToMany(Camp::class,'camp_tents_lookup','tent_id','camp_id');
    }

}

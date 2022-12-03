<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kitchen extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'kitchens';

    public function Location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    public function Camp()
    {
        return $this->belongsToMany(Camp::class,'camp_kitchen_lookup','kitchen_id','camp_id');
    }

}

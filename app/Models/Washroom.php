<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Washroom extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'washrooms';

    public function Location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    public function Camp()
    {
        return $this->belongsToMany(Camp::class,'camp_washroom_lookup','washroom_id','camp_id');
    }

}

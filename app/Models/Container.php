<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    use HasFactory;
    protected $table = 'containers';
    protected $guarded = [];

    public function Camp()
    {
        return $this->belongsToMany(Camp::class,'camp_containers_lookup','container_id','camp_id');
    }
    public function Location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}

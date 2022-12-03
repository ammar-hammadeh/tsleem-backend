<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'zones';
    protected $appends = [
        'name'
    ];
    public function getNameAttribute($val)
    {
        return $this->id;
    }
}

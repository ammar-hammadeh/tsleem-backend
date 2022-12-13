<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// use Spatie

class Square extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = ['name'];
    protected $table = 'square';

    protected static $logName = 'Square';
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name']);
    }


    public function camps()
    {
        return $this->hasMany(Camp::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plot extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'plots';

    public function Establishment()
    {
        return $this->belongsToMany(Establishment::class, 'establishment_plots_lookup', 'plot_id', 'establishment_id');
    }
    public function Zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

}

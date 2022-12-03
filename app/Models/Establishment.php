<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Establishment extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'establishments';

    public function Plot()
    {
        return $this->belongsToMany(Plot::class, 'establishment_plots_lookup', 'establishment_id', 'plot_id');
    }
}

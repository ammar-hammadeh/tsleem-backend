<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSign extends Model
{
    use HasFactory;
    protected $table = 'forms_signs';
    protected $guarded = [];
    protected $appends = [
        'full_path_sign'
    ];

    public function getFullPathSignAttribute($val)
    {
        if ($this->sign != null) {
            if (env('DISK') == 's3')
                return url(env('AWS_URL') . $this->sign);
            else
                return url('storage/' . $this->sign);
        } else {
            return $this->sign;
        }
    }
}

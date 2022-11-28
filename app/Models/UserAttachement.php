<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAttachement extends Model
{
    use HasFactory;
    protected $table = 'users_attachements';
    protected $guarded = [];

    public function getPathAttribute($val)
    {
        if ($val != null) {
            if (env('DISK') == 's3')
                return url(env('AWS_URL')  . $val);
            else
                return url('storage/' . $val);
        } else {
            return $val;
        }
    }
}

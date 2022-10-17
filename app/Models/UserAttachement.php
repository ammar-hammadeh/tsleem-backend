<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAttachement extends Model
{
    use HasFactory;
    protected $table = 'users_attachements';
    protected $guarded = [];

}

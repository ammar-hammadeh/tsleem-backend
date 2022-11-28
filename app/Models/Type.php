<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Entities\User;

class Type extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'status',
        'code',
        'deleted_at'
    ];

    protected $appends = [
        'status_text',
    ];
    public function getStatusTextAttribute($val)
    {
        return ($this->status == 'active') ? __('general.active') : __('general.disable');
    }

    public function getStatusAttribute($val)
    {
        return ($val == 'active') ? true : false;
    }

    public function setSignerAttribute($val)
    {
        $this->attributes['signer'] = ($val == 'true') ?  'active' : 'disabled';
    }

    public function users()
    {
        # code...
        return $this->hasMany(User::class, 'type_id');
    }

    public function getUser()
    {
        return $this->hasOne(User::class, 'type_id');
    }
}

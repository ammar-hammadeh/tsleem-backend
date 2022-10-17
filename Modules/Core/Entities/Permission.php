<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as spatiPermission;

class Permission extends spatiPermission
{
    protected $fillable = [
        'name',
        'guard_name',
    ];


    protected $appends = [
        'permission_lang',
    ];

    public function getPermissionLangAttribute()
    {
        return __('permission.' . $this->name);
    }

    // public function Roles(){
    //     return $this->belongsToMany('App\Role', 'role_permissions', 'permission_id', 'role_id');
    // }
}

<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = [
        'name',
    ];

    public function Permissions()
    {
        return $this->belongsToMany('App\Permission', 'role_permissions', 'role_id', 'permission_id');
    }

    public function syncPermissions($role,$permissions)
    {
       // $role->permissions()->detach();
        if(is_array($permissions)){
            foreach ($permissions as $permission) {
                $role->permissions()->attach($permission);
            }
        }else{
            $role->permissions()->attach($permissions);
        }

    }
}

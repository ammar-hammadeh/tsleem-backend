<?php

namespace Modules\Core\Entities;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;


class Role extends Model
{
    use LogsActivity;

    protected $table = 'roles';
    protected $fillable = [
        'name',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'Permissions.name'
            ])->useLogName('roles');
    }

    public function Permissions()
    {
        return $this->belongsToMany('App\Permission', 'role_permissions', 'role_id', 'permission_id');
    }

    public function syncPermissions($role, $permissions)
    {
        // $role->permissions()->detach();
        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                $role->permissions()->attach($permission);
            }
        } else {
            $role->permissions()->attach($permissions);
        }
    }
}

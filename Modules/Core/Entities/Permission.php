<?php

namespace Modules\Core\Entities;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission as spatiPermission;

class Permission extends spatiPermission
{
    use LogsActivity;
    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name'
            ])->useLogName('permissions');
    }



    protected $appends = [
        'permission_lang',
        'group_lang',
    ];

    public function getPermissionLangAttribute()
    {
        return __('permission.' . $this->name);
    }

    public function getGroupLangAttribute()
    {
        return __('permission.' . $this->group_page);
    }

    // public function Roles(){
    //     return $this->belongsToMany('App\Role', 'role_permissions', 'permission_id', 'role_id');
    // }
}

<?php

namespace Modules\Core\Database\Seeders;

use App\Models\Type;
use Modules\Core\Entities\User;
use Modules\Core\Entities\Permission;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Model::unguard();
        $permissions = Permission::all()->pluck('id')->toArray();
        $role = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $role->syncPermissions($permissions);
        $type_id = Type::whereCode('admin')->value('id');
        $user = User::create([
            'name' => 'super admin',
            'email' => 'admin@tasleem.com',
            'type_id' => $type_id,
            'status' => 'active',
            'phone' => '123123',
            'password' => bcrypt('102030'),
        ]);
        // $user = User::find(1);
        $user->assignRole($role);
    }
}

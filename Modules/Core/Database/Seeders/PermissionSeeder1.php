<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Entities\Permission;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PermissionSeeder1 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Model::unguard();
        Permission::insert(
            [
                // ['name' => 'dashboard', 'guard_name' => 'api'],
                ['name' => 'user-index', 'guard_name' => 'api'],
                ['name' => 'user-create', 'guard_name' => 'api'],
                ['name' => 'user-view', 'guard_name' => 'api'],
                ['name' => 'user-active', 'guard_name' => 'api'],
                ['name' => 'user-rejected', 'guard_name' => 'api'],
                ['name' => 'user-request', 'guard_name' => 'api'],
                ['name' => 'user-update-role', 'guard_name' => 'api'],

                ['name' => 'index-employee', 'guard_name' => 'api'],
                ['name' => 'add-employee', 'guard_name' => 'api'],

                ['name' => 'role-index', 'guard_name' => 'api'],
                ['name' => 'role-create', 'guard_name' => 'api'],
                ['name' => 'role-update', 'guard_name' => 'api'],
                // ['name' => 'role-delete', 'guard_name' => 'api'],

                ['name' => 'city-index', 'guard_name' => 'api'],
                ['name' => 'city-create', 'guard_name' => 'api'],
                ['name' => 'city-update', 'guard_name' => 'api'],
                ['name' => 'city-delete', 'guard_name' => 'api'],

                ['name' => 'type-index', 'guard_name' => 'api'],
                ['name' => 'type-update', 'guard_name' => 'api'],

                ['name' => 'camp-index', 'guard_name' => 'api'],
                ['name' => 'camp-create', 'guard_name' => 'api'],
                ['name' => 'camp-update', 'guard_name' => 'api'],
                ['name' => 'camp-delete', 'guard_name' => 'api'],
                ['name' => 'camp-status', 'guard_name' => 'api'],

                ['name' => 'square-index', 'guard_name' => 'api'],
                ['name' => 'square-create', 'guard_name' => 'api'],
                ['name' => 'square-update', 'guard_name' => 'api'],
                ['name' => 'square-delete', 'guard_name' => 'api'],

                ['name' => 'category-index', 'guard_name' => 'api'],
                ['name' => 'category-create', 'guard_name' => 'api'],
                ['name' => 'category-update', 'guard_name' => 'api'],
                ['name' => 'category-delete', 'guard_name' => 'api'],

                // ['name' => 'engineer-office-index', 'guard_name' => 'api'],
                // ['name' => 'engineer-office-create', 'guard_name' => 'api'],
                // ['name' => 'engineer-office-update', 'guard_name' => 'api'],
                // ['name' => 'engineer-office-delete', 'guard_name' => 'api'],


                ['name' => 'question-index', 'guard_name' => 'api'],
                ['name' => 'question-create', 'guard_name' => 'api'],
                ['name' => 'question-update', 'guard_name' => 'api'],
                ['name' => 'question-delete', 'guard_name' => 'api'],

                ['name' => 'tamplate-form-index', 'guard_name' => 'api'],
                ['name' => 'tamplate-form-create', 'guard_name' => 'api'],
                ['name' => 'tamplate-form-update', 'guard_name' => 'api'],
                ['name' => 'tamplate-form-delete', 'guard_name' => 'api'],

                ['name' => 'assign-index', 'guard_name' => 'api'],
                ['name' => 'assign-create', 'guard_name' => 'api'],
                ['name' => 'assign-update', 'guard_name' => 'api'],
                ['name' => 'assign-delete', 'guard_name' => 'api'],
                ['name' => 'assign-re-customization', 'guard_name' => 'api'],

                ['name' => 'signature-show-file', 'guard_name' => 'api'],
                ['name' => 'signature-index', 'guard_name' => 'api'],
                ['name' => 'signature-contract', 'guard_name' => 'api'],

                ['name' => 'appointment-create-index', 'guard_name' => 'api'],
                ['name' => 'appointment-index', 'guard_name' => 'api'],
                ['name' => 'appointment-send', 'guard_name' => 'api'],
                ['name' => 'appointment-update', 'guard_name' => 'api'],
                ['name' => 'appointment-contract', 'guard_name' => 'api'],
                ['name' => 'appointment-form-view', 'guard_name' => 'api'],
                ['name' => 'appointment-form-answer', 'guard_name' => 'api'],
                ['name' => 'appointment-form-answer-edit', 'guard_name' => 'api'],
                ['name' => 'contruct-bulk', 'guard_name' => 'api'],
                ['name' => 'contruct-all', 'guard_name' => 'api'],
                ['name' => 'signature-bulk', 'guard_name' => 'api'],
                ['name' => 'signature-all', 'guard_name' => 'api'],
                ['name' => 'delivery-index', 'guard_name' => 'api'],

            ]
        );
    }
}

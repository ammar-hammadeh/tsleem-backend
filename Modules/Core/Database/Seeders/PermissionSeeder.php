<?php

namespace Modules\Core\Database\Seeders;

use Modules\Core\Entities\Permission;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Model::unguard();
        $permissions =
            [
                'user-index',
                'user-create',
                'user-view',
                'user-active',
                'user-rejected',
                'user-request',
                'user-update-role',
                'user-active-account',
                'user-disabled-account',
                'delete_user',

                'index-employee',
                'add-employee',

                'role-index',
                'role-create',
                'role-update',
                'role-delete',

                // 'city-index',
                // 'city-create',
                // 'city-update',
                // 'city-delete',

                'type-index',
                'type-update',

                'camp-index',
                'camp-create',
                'camp-update',
                'camp-delete',
                'camp-status',

                'square-index',
                'square-create',
                'square-update',
                'square-delete',

                'category-index',
                'category-create',
                'category-update',
                'category-delete',

                'question_category-index',
                'question_category-create',
                'question_category-update',
                'question_category-delete',

                'question-index',
                'question-create',
                'question-update',
                'question-delete',

                'tamplate-form-index',
                'tamplate-form-create',
                'tamplate-form-update',
                'tamplate-form-delete',

                'assign-index',
                'assign-create',
                'assign-update',
                'assign-delete',
                'assign-re-customization',

                'signature-show-file',
                'signature-index',
                'signature-all',
                'signature-bulk',

                'appointment-creat-index',
                'appointment-create',
                'appointment-index',
                // 'appointment-send',
                'appointment-update',
                'appointment-form-view',
                'appointment-form-answer',
                // 'appointment-form-answer-edit',

                'appointment-contract',
                'signature-contract',
                'contruct-all',
                'contruct-bulk',

                // 'delivery-index',
                'delivery-sign',
                'delivery-view',


                'location-create',
                'location-update',
                'location-index',
                'location-delete',

                'kitchen-index',
                'kitchen-create',
                'kitchen-update',
                'kitchen-delete',

                'tent-index',
                'tent-create',
                'tent-update',
                'tent-delete',


                'plot-index',
                'plot-create',
                'plot-update',
                'plot-delete',


                'establishments-index',
                'establishments-create',
                'establishments-update',
                'establishments-delete',

                'container-index',
                'container-create',
                'container-update',
                'container-delete',

                'water-tape-index',
                'water-tape-create',
                'water-tape-update',
                'water-tape-delete',

                'zone-index',
                'zone-create',

                'washroom-index',
                'washroom-create',
                'washroom-update',
                'washroom-delete',

                'electrical-meter-index',
                'electrical-meter-create',
                'electrical-meter-update',
                'electrical-meter-delete',

                'report-log',


            ];

        $permissions_page = [
            'user', 'user', 'user', 'user', 'user', 'user', 'user',
            'user', 'user',
            'employee', 'employee',
            'role', 'role', 'role', 'role',
            'city', 'city', 'city', 'city',
            'type', 'type',
            'camp', 'camp', 'camp', 'camp', 'camp',
            'square', 'square', 'square', 'square',
            'category', 'category', 'category', 'category',
            'question_category', 'question_category', 'question_category', 'question_category',
            'question', 'question', 'question', 'question',
            'tamplate', 'tamplate', 'tamplate', 'tamplate',
            'assign', 'assign', 'assign', 'assign', 'assign',
            'signature', 'signature', 'signature', 'signature',
            'appointment', 'appointment', 'appointment', 'appointment', 'appointment', 'appointment',
            'contruct', 'contruct', 'contruct', 'contruct',
            'delivery', 'delivery',
            'location', 'location', 'location', 'location',
            'kitchen', 'kitchen', 'kitchen', 'kitchen',
            'tent', 'tent', 'tent', 'tent',
            'plot', 'plot', 'plot', 'plot',
            'establishments', 'establishments', 'establishments', 'establishments',
            'container', 'container', 'container', 'container',
            'water-tape', 'water-tape', 'water-tape', 'water-tape',
            'zone', 'zone',
            'washroom', 'washroom', 'washroom', 'washroom',
            'electrical-meter', 'electrical-meter', 'electrical-meter', 'electrical-meter',
            'report'
        ];
        $result = array();

        for ($i = 0; $i < count($permissions); $i++) {

            array_push($result, ['name' => $permissions[$i], 'group_page' => $permissions_page[$i], 'guard_name' => 'api']);
        }

        Permission::insert($result);
    }
}

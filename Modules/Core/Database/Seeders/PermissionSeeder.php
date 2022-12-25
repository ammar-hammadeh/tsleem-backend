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
                // مرحلة التثبيت
                // 'signature-show-file',
                // 'signature-index',
                // 'signature-all',
                // 'signature-bulk',

                'appointment-creat-index',
                'appointment-create',
                'appointment-index',
                // 'appointment-send',
                'appointment-update',
                'appointment-form-view',
                'appointment-form-answer',
                // 'appointment-form-answer-edit',

                // تثبيت

                'appointment-contract',
                'signature-contract',
                'contruct-all',
                'contruct-bulk',

                // 'delivery-index',
                'delivery-sign',
                'delivery-view',


                'gis-location-create',
                'gis-location-update',
                'gis-location-index',
                'gis-location-delete',

                'gis-kitchen-index',
                'gis-kitchen-create',
                'gis-kitchen-update',
                'gis-kitchen-delete',

                'gis-tent-index',
                'gis-tent-create',
                'gis-tent-update',
                'gis-tent-delete',


                'gis-plot-index',
                'gis-plot-create',
                'gis-plot-update',
                'gis-plot-delete',


                'gis-establishments-index',
                'gis-establishments-create',
                'gis-establishments-update',
                'gis-establishments-delete',

                'gis-container-index',
                'gis-container-create',
                'gis-container-update',
                'gis-container-delete',

                'gis-water-tape-index',
                'gis-water-tape-create',
                'gis-water-tape-update',
                'gis-water-tape-delete',

                'gis-zone-index',
                'gis-zone-create',

                'gis-washroom-index',
                'gis-washroom-create',
                'gis-washroom-update',
                'gis-washroom-delete',

                'gis-electrical-meter-index',
                'gis-electrical-meter-create',
                'gis-electrical-meter-update',
                'gis-electrical-meter-delete',

                'gis-map-index',

                'report-log',
                'report-system-log'

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
            // 'signature', 'signature', 'signature', 'signature',
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
            'map',
            'report', 'report'
        ];
        $result = array();

        for ($i = 0; $i < count($permissions); $i++) {

            array_push($result, ['name' => $permissions[$i], 'group_page' => $permissions_page[$i], 'guard_name' => 'api']);
        }

        Permission::insert($result);
    }
}

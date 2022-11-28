<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Type::create([
            'name' => 'مدير نظام',
            'code' => 'admin',
            'name_in_form' => '',
        ]);
        Type::create([
            'name' => 'شركات حجاج الداخل',
            'code' => 'service_provider',
            'name_in_form' => 'ممثل الجهة المستفيدة'
        ]);
        Type::create([
            'name' => 'مكتب هندسي',
            'code' => 'design_office',
            'name_in_form' => ''
        ]);
        Type::create([
            'name' => 'جهة مشاركة',
            'code' => 'sharer',
            'name_in_form' => 'مندوب وزارة الحج',
        ]);
        // Type::create([
        //     'name' => 'مكتب استشاري',
        //     'code' => 'consulting_office',
        //     'name_in_form' => 'مندوب الاستشاري'
        // ]);
        Type::create([
            'name' => 'مقاول',
            'code' => 'contractor',
            'name_in_form' => 'ممثل المقاول'
        ]);
        Type::create([
            'name' => 'شركة طوافة',
            'code' => 'raft_company',
            'name_in_form' => 'ممثل الجهة المستفيدة'
        ]);
        Type::create([
            'name' => 'صيانة تسليم',
            'code' => 'maintenance',
            'name_in_form' => ''
        ]);
        Type::create([
            'name' => 'تسليم',
            'code' => 'delivery',
            'name_in_form' => ''
        ]);
        Type::create([
            'name' => 'كدانة',
            'code' => 'kdana',
            'name_in_form' => 'مندوب شركة كدانة للتنمية والتطوير',
        ]);
        Type::create([
            'name' => 'مركز خدمة',
            'code' => 'raft_office',
            'name_in_form' => 'ممثل الجهة المستفيدة'
        ]);
    }
}

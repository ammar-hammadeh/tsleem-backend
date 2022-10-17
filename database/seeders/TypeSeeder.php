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
            'signer' => 0,
            'code' => 'admin'
        ]);
        Type::create([
            'name' => 'شركات حجاج الداخل',
            'signer' => 0,
            'code' => 'service_provider'
        ]);
        Type::create([
            'name' => 'مكتب هندسي',
            'signer' => 0,
            'code' => 'design_office'
        ]);
        Type::create([
            'name' => 'جهة مشاركة',
            'signer' => 0,
            'code' => 'sharer'
        ]);
        Type::create([
            'name' => 'مكتب استشاري',
            'signer' => 0,
            'code' => 'consulting_office'
        ]);
        Type::create([
            'name' => 'مقاول',
            'signer' => 0,
            'code' => 'contractor'
        ]);
        Type::create([
            'name' => 'شركة طوافة',
            'signer' => 0,
            'code' => 'raft_company'
        ]);
        Type::create([
            'name' => 'صيانة تسليم',
            'signer' => 0,
            'code' => 'maintenance'
        ]);
        Type::create([
            'name' => 'تسليم',
            'signer' => 0,
            'code' => 'delivery'
        ]);
        Type::create([
            'name' => 'كدانة',
            'signer' => 1,
            'code' => 'kdana'
        ]);
        Type::create([
            'name' => 'مكتب طوافة',
            'signer' => 0,
            'code' => 'raft_office'
        ]);
    }
}

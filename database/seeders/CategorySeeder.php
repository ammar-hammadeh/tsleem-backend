<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::create([
            'name' => 'شركة مطوفي حجاج جنوب شرق اسيا',
            'type_id' => '7'
        ]);

        Category::create([
            'name' => 'شركة مطوفي حجاج جنوب اسيا',
            'type_id' => '7'
        ]);

        Category::create([
            'name' => 'شركة مطوفي حجاج افريقيا غير العربية',
            'type_id' => '7'
        ]);

        Category::create([
            'name' => 'شركة مطوفي حجاج الدول العربية',
            'type_id' => '7'
        ]);
        Category::create([
            'name' => 'شركة مطوفي حجاج تركيا وحجاج اوروبا وامريكا واستراليا',
            'type_id' => '7'
        ]);
        Category::create([
            'name' => 'شركة مطوفي حجاج ايران',
            'type_id' => '7'
        ]);
        Category::create([
            'name' => 'المجلس التنسيقي لمؤسسات وشركات خدمة حجاج الداخل',
            'type_id' => '7'
        ]);

        
        Category::create([
            'name' => 'إشراف',
            'type_id' => '3'
        ]);
        Category::create([
            'name' => 'الحماية والوقاية من الحريق',
            'type_id' => '3'
        ]);

        Category::create([
            'name' => 'مكتب تصميم',
            'type_id' => '3'
        ]);

        Category::create([
            'name' => 'عام',
            'type_id' => '6'
        ]);
        Category::create([
            'name' => 'الوقاية والحماية من الحرائق',
            'type_id' => '6'
        ]);
 


    }
}

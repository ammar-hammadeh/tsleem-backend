<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        City::create([
            'name' => 'تبوك',
        ]);
        City::create([
            'name' => 'الرياض',
        ]);
        City::create([
            'name' => 'مكة',
        ]);
    }
}

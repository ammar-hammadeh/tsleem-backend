<?php

namespace Database\Seeders;

use App\Models\Input;
use Illuminate\Database\Seeder;

class InputSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Input::create([
            'name' => 'text',
            'type' => 'text',
        ]);
        Input::create([
            'name' => 'date',
            'type' => 'date',
        ]);
        Input::create([
            'name' => 'number',
            'type' => 'number',
        ]);
        Input::create([
            'name' => 'checkbox',
            'type' => 'checkbox',
        ]);

    }
}

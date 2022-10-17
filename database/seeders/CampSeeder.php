<?php

namespace Database\Seeders;

use App\Models\Camp;
use App\Models\Square;
use Illuminate\Database\Seeder;

class CampSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $square = collect(Square::get());
        for ($i = 0; $i < 30; $i++) {
            $selected = $square->random();
            $camps[] = [
                'square_id' => $selected->id,
                'name' => 'المخيم' . $i + 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        $chunks = array_chunk($camps, 30);
        foreach ($chunks as $chunk) {
            Camp::insert($chunk);
        }
    }
}

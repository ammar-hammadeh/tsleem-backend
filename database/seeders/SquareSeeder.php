<?php

namespace Database\Seeders;

use App\Models\Square;
use Illuminate\Database\Seeder;

class SquareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        for ($i = 0; $i < 7; $i++) {
            $camps[] = [
                'name' => 'المربع' . $i + 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        $chunks = array_chunk($camps, 7);
        foreach ($chunks as $chunk) {
            Square::insert($chunk);
        }
    }
}

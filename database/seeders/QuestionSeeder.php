<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Input;
use App\Models\Question;
use App\Models\Type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $input = collect(Input::get());
        for ($i = 0; $i < 30; $i++) {
            $selected = $input->random();
            $ata[] = [
                'input_id' => $selected->id,
                'title' => 'سؤال ' . $i + 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        $chunks = array_chunk($ata, 30);
        foreach ($chunks as $chunk) {
            Question::insert($chunk);
        }
    }
}

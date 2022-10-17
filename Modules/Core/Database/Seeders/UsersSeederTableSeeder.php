<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Entities\User;

class UsersSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call("OthersTableSeeder");
        for ($i = 0; $i <= 100; $i++) {
            User::create([
                'name' => 'test' . $i,
                'email' => 'a' . $i . '@a.a',
                'password' => bcrypt('102030'),
            ]);
        }
    }
}

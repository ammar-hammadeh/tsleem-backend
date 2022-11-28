<?php

namespace Modules\Core\Database\Seeders;

use App\Models\Company;
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
        // for ($i = 0; $i <= 100; $i++) {
        //     User::create([
        //         'name' => 'test' . $i,
        //         'email' => 'a' . $i . '@a.a',
        //         'password' => bcrypt('102030'),
        //     ]);
        // }

        // $table->unsignedBigInteger('company_id')->nullable();
        // $table->string('name');
        // $table->string('hardcopyid')->nullable();
        // $table->string('phone')->nullable();
        // $table->string('signature')->nullable();
        // $table->string('email')->unique();
        // $table->timestamp('email_verified_at')->nullable();
        // $table->string('password');
        // $table->string('avatar')->nullable();
        // $table->string('reject_reason')->nullable();
        // $table->enum('status', ['pending', 'active', 'disabled', 'rejected'])->default('pending');
        // $table->rememberToken();
        // $table->timestamp('deleted_at')->nullable();


        // $table->unsignedBigInteger('type_id')->nullable();
        // $table->unsignedBigInteger('owner_id')->nullable();
        // $table->unsignedBigInteger('parent_id')->nullable();
        // // $table->unsignedBigInteger('engineer_office_id')->nullable();
        // $table->string('kroky')->nullable();
        // $table->string('prefix')->nullable();
        // $table->string('name')->nullable();
        // $table->string('commercial')->nullable();
        // $table->string('license')->nullable();
        // $table->string('owner_name')->nullable();
        // $table->string('owner_hardcopyid')->nullable();
        // $table->timestamp('commercial_expiration')->nullable();
        // $table->timestamp('deleted_at')->nullable();


        $user1 = User::create([
            'name' => 'asia_east',
            'email' => 'oedrees@sea.com.sa',
            'type_id' => 7,
            'status' => 'active',
            'phone' => '0555504970',
            'status' => 'active',
            'password' => '$2y$10$siJ.vmD.e87cY4TZP4rRQuXLPKy7QkCyUlb5pSsmf2dbUI5CfneQi',
        ]);
        $user2 = User::create([
            'name' => 'asia_south',
            'email' => 'mahafiz@mhsae.com',
            'type_id' => 7,
            'status' => 'active',
            'phone' => '0555545838',
            'status' => 'active',
            'password' => '$2y$10$eK89cFydKLTzqI75CNH6lOwrKLY1xVOUuKMcO4dlt3gvIKVc41Z6W',
        ]);

        $user3 = User::create([
            'name' => 'africa',
            'email' => 'holymina93@gmail.com',
            'type_id' => 7,
            'status' => 'active',
            'phone' => '0501475014',
            'status' => 'active',
            'password' => '$2y$10$3ZP0IzDg9Pic4LUhTihpTefoaDae6jpzrQ3xMtmUJkEpz1CP0bVri',
        ]);

        $user4 = User::create([
            'name' => 'arabic',
            'email' => 'arabic@gmail.com',
            'type_id' => 7,
            'status' => 'active',
            'phone' => '0555018155',

            'status' => 'active',
            'password' => '$2y$10$uhiO.RUV/bhw8yv.ChevNuk3JxLF980S9faKZuzZWZBJTXxW5xXMO',
        ]);

        $user5 = User::create([
            'name' => 'turkey',
            'email' => 'almashaer.mina@gmail.com',
            'type_id' => 7,
            'status' => 'active',
            'phone' => '0535608090',

            'status' => 'active',
            'password' => '$2y$10$ysWDHLbmrNqMwIMzangyoO8fPU4oEQKzAZuTW69.0WLt9hGY00Rj6',
        ]);

        $user6 = User::create([
            'name' => 'iran',
            'email' => 'iran@gmail.com',
            'type_id' => 7,
            'status' => 'active',
            'phone' => '0544332244',
            'status' => 'active',
            'password' => '$2y$10$kl2LYp9A6AuWHZrd1nGzHuQB.16nw5H4xs3XC5sh4dF1KAM9UA8gq',
        ]);

        $user7 = User::create([
            'name' => 'coordinating',
            'email' => 'coordinating@gmail.com',
            'type_id' => 7,
            'status' => 'active',
            'phone' => '0555500043',

            'status' => 'active',
            'password' => '$2y$10$5PYwbmir19RbnGb44CLy/e5SzPbIfxm88ukETtsmk8tEMRxUvtnZy',
        ]);





        $company1 = Company::create([
            'name' => 'asia_east',
            'type_id' => 7,
            'owner_id' => $user1->id,
            'kroky' => '440224002',
            'prefix' => 'EA',
        ]);
        $company2 = Company::create([
            'name' => 'asia_south',
            'type_id' => 7,
            'owner_id' => $user2->id,
            'kroky' => '440224001',
            'prefix' => 'SA',
        ]);
        $company3 = Company::create([
            'name' => 'africa',
            'type_id' => 7,
            'owner_id' => $user3->id,
            'kroky' => '440224004',
            'prefix' => 'AF',
        ]);
        $company4 = Company::create([
            'name' => 'arabic',
            'type_id' => 7,
            'owner_id' => $user4->id,
            'kroky' => '440224003',
            'prefix' => 'AR',
        ]);
        $company5 = Company::create([
            'name' => 'turkey',
            'type_id' => 7,
            'owner_id' => $user5->id,
            'kroky' => '440224005',
            'prefix' => 'TR',
        ]);
        $company6 = Company::create([
            'name' => 'iran',
            'type_id' => 7,
            'owner_id' => $user6->id,
            'kroky' => '440224006',
            'prefix' => 'IR',
        ]);

        $company7 = Company::create([
            'name' => 'coordinating',
            'type_id' => 7,
            'owner_id' => $user7->id,
            'kroky' => '',
            'prefix' => 'COR',
        ]);



        for ($i = 1; $i <= 7; $i++) {
            ${'user' . $i}->update([
                'company_id' => ${'company' . $i}->id
            ]);
        }
    }
}

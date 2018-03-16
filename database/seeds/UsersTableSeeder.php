<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\User::query()->updateOrCreate(
            ['email' => 'hoangnam0705@icloud.com'],
            [
                'name'     => 'Nam',
                'email'    => 'hoangnam0705@icloud.com',
                'password' => bcrypt('secret'),
            ]
        );

        \App\User::query()->updateOrCreate(
            ['email' => 'davidhazeland@gmail.com'],
            [
                'name'     => 'David',
                'email'    => 'davidhazeland@gmail.com',
                'password' => bcrypt('secret'),
            ]
        );
    }
}

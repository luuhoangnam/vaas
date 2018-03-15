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
        \App\User::query()->create([
            'name'     => 'Nam',
            'email'    => 'hoangnam0705@icloud.com',
            'password' => bcrypt('secret'),
        ]);
    }
}

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
        $user = App\Models\User::create([
            'email'     => 'user@domain.com',
            'password'  => bcrypt('password'),
        ]);
    }
}

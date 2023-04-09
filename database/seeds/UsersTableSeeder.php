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
	    DB::table('users')->insert([
	        'admin'    => 1,
            'name'     => 'Admin',
            'email'    => 'admin@test.com',
            'password' => bcrypt('admin2023')
        ]);
    }
}

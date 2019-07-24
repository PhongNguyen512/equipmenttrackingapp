<?php

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        DB::table('users')->insert([
            'name' => $faker->name,
            'email' => 'admin@abc.com',
            'user_role_id' => 1,
            'password' => bcrypt('secret'),
        ]);

        DB::table('users')->insert([
            'name' => $faker->name,
            'email' => 'coordinator@abc.com',
            'user_role_id' => 2,
            'password' => bcrypt('secret'),
        ]);
        
    	foreach (range(1,5) as $index) {
	        DB::table('users')->insert([
	            'name' => $faker->name,
                'email' => $faker->email,
                'user_role_id' => 3,
	            'password' => bcrypt('secret'),
	        ]);
	    }
    }
}

<?php

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Jason Tood',
            'email' => 'admin@abc.com',
            'user_role_id' => 1,
            'password' => bcrypt('secret'),
        ]);

        DB::table('users')->insert([
            'name' => 'Bruce Wayne',
            'email' => 'coordinator@abc.com',
            'user_role_id' => 2,
            'password' => bcrypt('secret'),
        ]);

        for($i = 1; $i < 5; $i++){
            DB::table('users')->insert([
	            'name' => 'Foremen '.$i,
                'email' => 'user'.$i.'@abc.com',
                'user_role_id' => 3,
	            'password' => bcrypt('secret'),
	        ]);
        }
    }
}

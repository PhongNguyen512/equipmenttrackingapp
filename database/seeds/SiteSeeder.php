<?php

use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sites')->insert([
            'site_name' => 'SUPERCORE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('sites')->insert([
            'site_name' => 'AURORA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('sites')->insert([
            'site_name' => 'KEARL',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('sites')->insert([
            'site_name' => 'SUNCOR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('sites')->insert([
            'site_name' => 'LOWLAND',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

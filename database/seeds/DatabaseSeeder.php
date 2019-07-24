<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SiteSeeder::class);
        $this->call(EquipmentClassSeeder::class);
        $this->call(EquipmentSeeder::class);
        $this->call(UserRoleSeeder::class);
        $this->call(UserSeeder::class);
    }
}

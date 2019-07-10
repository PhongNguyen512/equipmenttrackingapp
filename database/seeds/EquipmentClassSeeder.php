<?php

use Illuminate\Database\Seeder;

class EquipmentClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i = 1; $i < 6; $i++){
            DB::table('equipment_classes')->insert([
                'billing_rate' => 'B0'.$i,
                'equipment_class_name' => 'Articulating Trucks - '.($i*10).'T',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

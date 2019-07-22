<?php

use Illuminate\Database\Seeder;
use App\EquipmentClass;
use App\Site;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        for($i = 0; $i < 10; $i++){
            DB::table('equipments')->insert([
                'unit' => 'AT'.($i+110),
                'description' => 'SMT-'.($i+1),
                'ltd_smu' => rand(15236, 1547985),
                'owning_status' => Arr::random(['OWN', 'RENT']),
                'equipment_status' => Arr::random(['AV', 'DM']),
                'mechanical_status' => Arr::random(['Tailgate', '', '', 'Proheat', 'Bed liner', '', 'On Holde', '']),
                'equipment_class_id' => rand(1, 5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

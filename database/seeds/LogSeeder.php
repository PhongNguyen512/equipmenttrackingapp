<?php

use Illuminate\Database\Seeder;

class LogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('equip_update_logs')->insert([
            'date' => now()->format('d M'),
            'shift' => "Day",
            'smu' => rand(15236, 1547985),
            'unit' => 'AT1111',
            'equipment_class' => 'Class 01',
            'start_of_shift_status' => 'AV',
            'current_status' => 'AV',
            'equipment_id' => 1,
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

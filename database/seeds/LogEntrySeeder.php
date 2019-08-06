<?php

use Illuminate\Database\Seeder;
use App\Equipment;
use Carbon\Carbon;

class LogEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $equipment = Equipment::find(1);
        $equipClass = $equipment->EquipmentClassList()->first();

        for($i = 0; $i < 10; $i++){

            $downAt = date("H:i", strtotime(rand(7, 13).":00"));
            $upAt = Carbon::parse($downAt)->addMinute(rand(100,300))->format("H:i");

            $time_diff = date_diff( date_create($downAt), date_create($upAt) );
            $downTime = round(($time_diff->h * 60 + $time_diff->i) / 60, 2) ;
            $parkTime = 12 - $downTime;

            if( date('H', strtotime($downAt)) >= 7 && date('H', strtotime($downAt)) < 9 )
                $start_of_shift = "DM";
            else
                $start_of_shift = "AV";

            $date = Carbon::now()->sub( (10-$i), 'day')->format('d M');

            DB::table('equip_update_logs')->insert([
                'date' => $date,
                'shift' => 'Day',
                'smu' => '123456789',
                'unit' => $equipment->unit,
                'equipment_class' => $equipClass->billing_rate.' '.$equipClass->equipment_class_name,
                'summary' => $equipClass->billing_rate,
                'parked_hrs' => $parkTime,
                'down_hrs' => $downTime,
                'down_at' => $downAt,
                'up_at' => $upAt,
                'start_of_shift_status' => $start_of_shift,
                'current_status' => 'AV',
                'equipment_id' => $equipment->id,
                'user_id' => 1,
                'created_at' => Carbon::parse($date)
            ]);

            $i++;

            $date = Carbon::now()->sub( (10-$i), 'day')->format('d M');
            DB::table('equip_update_logs')->insert([
                'date' => $date,
                'shift' => 'Day',
                'smu' => '1234567',
                'unit' => $equipment->unit,
                'equipment_class' => $equipClass->billing_rate.' '.$equipClass->equipment_class_name,
                'summary' => $equipClass->billing_rate,
                'down_at' => $downAt,
                'start_of_shift_status' => "DM",
                'current_status' => 'DM',
                'equipment_id' => $equipment->id,
                'user_id' => 1,
                'created_at' => Carbon::parse($date)
            ]);

        }
    }
}

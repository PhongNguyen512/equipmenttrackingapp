<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\EquipUpdateLog;
use Illuminate\Support\Facades\DB;
use App\Equipment;

class ForcedLogDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:forced';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force Log AV equipment daily at midnight';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $checkLog = json_decode(DB::table('equip_update_logs')
                    ->select('unit')
                    ->where('date', '=', now()->format('d M'))
                    ->where('current_status', '=', 'AV')
                    ->get(), true);

        $equipmentList = DB::table('equipments')
                    ->where('equipment_status', '=', 'AV')
                    ->whereNotIn('unit', $checkLog)
                    ->get();

        foreach($equipmentList as $equipment){
            $equipment = Equipment::find($equipment->id);
            $equipClass = $equipment->EquipmentClassList()->first();

            EquipUpdateLog::create([
                'date' => now()->format('d M'),
                'shift' => '',
                'smu' => $equipment->ltd_smu,
                'unit' => $equipment->unit,
                'class' => $equipClass->billing_rate.' '.$equipClass->equipment_class_name,
                'summary' => $equipClass->billing_rate,
                'start_of_shift_status' => 'AV',
                'current_status' => 'AV',
                'comments' =>  '',
                'down_at' => '',
                'time_entry' => now()->format("H:i"),
                'location' => '',
                'updated_at' => now(),
                'equipment_id' => $equipment->id
            ]);

        }
        
    }
}

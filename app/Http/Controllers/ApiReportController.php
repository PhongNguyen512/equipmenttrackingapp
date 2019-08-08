<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Equipment;
use App\EquipmentClass;
use Illuminate\Support\Facades\DB;

class ApiReportController extends Controller
{
    /////////
    // Get Report 
    ////////
    public function getReport(Site $site){

        $object = new \stdClass();
        $object->id = $site->id;
        $object->site_name = $site->site_name;
        $object->equipment_class_list = $this->getEquipClass($site->EquipmentClassList()->get());      

        return response()->json($object);
    }
    
    private function getEquipClass($data){
        $returnData = [];

        foreach($data as $d){
            $object = new \stdClass();
            $object->equipment_class_name = $d->equipment_class_name;
            $object->equipment_list = $this->getEquip($d->EquipmentList()->get());

            array_push($returnData, $object);
        }

        return $returnData;
    }

    private function getEquip($data){
        $returnData = [];

        foreach($data as $d){
            $object = new \stdClass();
            $object->unit = $d->unit;
            $object->equipment_status = $d->equipment_status;

            $logEntry = DB::table('equip_update_logs')
                        ->where('unit', '=', $d->unit)
                        ->latest()
                        ->first();

            if($logEntry !== null){
                if($d->equipment_status === 'DM')
                    $object->est_date_of_repair = $logEntry->est_date_of_repair;

                $object->note = $logEntry->comments;
            }
            else{
                $object->est_date_of_repair = null;
                $object->note = null;
            }

            $object->additional_detail = isset($d->additional_detail) ? $d->additional_detail : null;

            array_push($returnData, $object);
        }
        return $returnData;
    }

}

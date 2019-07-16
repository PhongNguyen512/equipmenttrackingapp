<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Equipment;
use App\EquipmentClass;

class ApiGetController extends Controller
{
    /////////
    // All in 1 
    ////////
    public function allData(){
        $sites = Site::all();

        $finalData = [];

        foreach($sites as $site){
            $object = new \stdClass();
            $object->id = $site->id;
            $object->site_name = $site->site_name;
            $object->equipment_class_list = $this->getEquipClass($site->EquipmentClassList()->get());

            array_push($finalData, $object);
        }

        return response()->json($finalData);
    }

    private function getEquipClass($data){
        $returnData = [];

        foreach($data as $d){
            $object = new \stdClass();
            $object->id = $d->id;
            $object->billing_rate = $d->billing_rate;
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
            $object->id = $d->id;
            $object->unit = $d->unit;
            $object->description = $d->description;
            $object->ltd_smu = $d->ltd_smu;
            $object->owning_status = $d->owning_status;
            $object->equipment_status = $d->equipment_status;
            $object->mechanical_status = $d->mechanical_status;

            array_push($returnData, $object);
        }

        return $returnData;
    }
    
}

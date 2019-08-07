<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Equipment;
use App\EquipmentClass;
use App\User;

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
            $object->lat = $d->lat;
            $object->lng = $d->lng;

            if($d->equipment_status === 'AV')
                $object->equipment_status = true;
            else
                $object->equipment_status = false;

            $object->mechanical_status = $d->mechanical_status;

            array_push($returnData, $object);
        }

        return $returnData;
    }

    /////////
    // All sites
    ////////
    public function allSites(){
        $sites = Site::all();
        $finalData = [];

        foreach($sites as $site){
            $object = new \stdClass();
            $object->id = $site->id;
            $object->site_name = $site->site_name;

            array_push($finalData, $object);
        }

        return response()->json($finalData);
    }

    /////////
    // All equipment classes
    ////////
    public function allEquipClass(){
        $equipClasses = EquipmentClass::all();
        $finalData = [];

        foreach($equipClasses as $equipClass){
            $object = new \stdClass();
            $object->id = $equipClass->id;
            $object->billing_rate = $equipClass->billing_rate;
            $object->equipment_class_name = $equipClass->equipment_class_name;

            array_push($finalData, $object);
        }

        return response()->json($finalData);
    }

    /////////
    // All equipment 
    ////////
    public function allEquip(){
        $equipments = Equipment::all();
        $finalData = [];

        foreach($equipments as $equip){
            $object = new \stdClass();
            $object->id = $equip->id;
            $object->unit = $equip->unit;
            $object->description = $equip->description;
            $object->ltd_smu = $equip->ltd_smu;
            $object->owning_status = $equip->owning_status;

            if($equip->equipment_status === 'AV')
                $object->equipment_status = true;
            else
                $object->equipment_status = false;

            $object->machanical_status = $equip->machanical_status;

            array_push($finalData, $object);
        }

        return response()->json($finalData);
    }

    /////////
    // All user 
    ////////
    public function allUser(){
        $users = User::all();
        $finalData = [];

        foreach($users as $user){
            $object = new \stdClass();
            $object->id = $user->id;
            $object->name = $user->name;

            array_push($finalData, $object);
        }

        return response()->json($finalData);
    }

}

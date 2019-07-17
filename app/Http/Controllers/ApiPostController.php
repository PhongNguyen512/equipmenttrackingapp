<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Equipment;
use App\EquipmentClass;
use Illuminate\Support\Facades\Validator;

class ApiPostController extends Controller
{
    ////////////
    // require => site_name
    ///////////
    public function newSite(Request $request){
        //validate input value
        $validator = Validator::make($request->all(), [
            'site_name' => ['required', 'unique:sites'],
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => $validator->messages()->first(),
            ]);
        }

        Site::create([
            'site_name' => $request->site_name,
        ]);

        $newSite = Site::latest()->first();

        return response()->json([
            'success' => 'New site has been added',
            'new data' => $newSite,
        ]);
    }

    ////////////
    // require => billing_rate, equipment_class_name, site_id
    ///////////
    public function newEquipmentClass(Request $request){
        //validate input value
        $validator = Validator::make($request->all(), [
            'billing_rate' => ['required'],
            'equipment_class_name' => ['required', 'unique:equipment_classes'],
            'site_id' => ['required'],
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => $validator->messages()->first(),
            ]);
        }

        EquipmentClass::create([
            'billing_rate' => $request->billing_rate,
            'equipment_class_name' => $request->equipment_class_name,
            'site_id' => $request->site_id,
        ]);

        $newEquipClass = EquipmentClass::latest()->first();

        return response()->json([
            'success' => 'New Equipment Class has been added',
            'new data' => $newEquipClass,
        ]);
    }

    ////////////
    // require => unit, ltd_smu, equipment_class_id
    ///////////
    public function newEquip(Request $request){
        //validate input value
        $validator = Validator::make($request->all(), [
            'unit' => ['required', 'min:3', 'unique:equipments'],
            'ltd_smu' => ['required'],
            'equipment_class_id' => ['required'],
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => $validator->messages()->first(),
            ]);
        }

        Equipment::create([
            'unit' => $request->unit,
            'description' => $request->description !== null ? $request->description : '',
            'ltd_smu' => $request->ltd_smu,
            'owning_status' => $request->owning_status !== null ? $request->owning_status : 'RENT',
            'equipment_status' => $request->equipment_status !== null ? $request->equipment_status : 'AV',
            'mechanical_status' => $request->mechanical_status !== null ? $request->mechanical_status : '',
            'equipment_class_id' => $request->equipment_class_id,
        ]);

        $newEquip = Equipment::latest()->first();

        return response()->json([
            'success' => 'New Equipment has been added',
            'new data' => $newEquip,
        ]);
    }
    
}

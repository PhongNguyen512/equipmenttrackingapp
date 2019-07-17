<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Equipment;
use App\EquipmentClass;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

    public function updateSite(Request $request, Site $site){
        //if POST data is nothing
        if( count($request->all()) == 0 ){
            return response()->json([
                'error' => 'Updating data not found',
            ]);
        }

         //validate input value
         $validator = Validator::make($request->all(), [
            'site_name' => ['required', Rule::unique('sites', 'site_name')->ignore($site->id)],
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => $validator->messages()->first(),
            ]);
        }

        $oldData = Site::find($site->id);
        $site->site_name = $request->site_name;
        $site->save();

        return response()->json([
            'success' => 'A site has been updated',
            'old data' => $oldData,
            'new data' => $site,
        ]);
    }

    public function updateEquipmentClass(Request $request, EquipmentClass $equipClass){
        //if POST data is nothing
        if( count($request->all()) == 0 ){
            return response()->json([
                'error' => 'Updating data not found',
            ]);
        }

        //validate input value
        $validator = Validator::make($request->all(), [
            'equipment_class_name' => [Rule::unique('equipment_classes', 'equipment_class_name')->ignore($equipClass->id)],
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => $validator->messages()->first(),
            ]);
        }

        $oldData = EquipmentClass::find($equipClass->id);

        $equipClass->billing_rate = $request->billing_rate !== null ? $request->billing_rate : $equipClass->billing_rate;
        $equipClass->equipment_class_name = $request->equipment_class_name !== null ? $request->equipment_class_name : $equipClass->equipment_class_name;
        $equipClass->site_id = $request->site_id !== null ? $request->site_id : $equipClass->site_id;
        $equipClass->save();

        return response()->json([
            'success' => 'An Equipment Class has been updated',
            'old data' => $oldData,
            'new data' => $equipClass,
        ]);
    }

    public function updateEquip(Request $request, Equipment $equip){
        //if POST data is nothing
        if( count($request->all()) == 0 ){
            return response()->json([
                'error' => 'Updating data not found',
            ]);
        }

        //validate input value
        $validator = Validator::make($request->all(), [
            'unit' => ['min:3', Rule::unique('equipments', 'unit')->ignore($equip->id) ],
            'ltd_smu' => ['numeric'],
        ]);

        if($request->ltd_smu < $equip->ltd_smu){
            return response()->json([
                'error' => "SMU is invalid. Please contact coordinator or admin.",
            ]);
        }

        if($validator->fails()){
            return response()->json([
                'error' => $validator->messages()->first(),
            ]);
        }

        $oldData = Equipment::find($equip->id);

        $equip->unit = $request->unit !== null ? $request->unit : $equip->unit;
        $equip->description = $request->description !== null ? $request->description : $equip->description;
        $equip->ltd_smu = $request->ltd_smu !== null ? $request->ltd_smu : $equip->ltd_smu;
        $equip->owning_status = $request->owning_status !== null ? $request->owning_status : $equip->owning_status;
        $equip->equipment_status = $request->equipment_status !== null ? $request->equipment_status : $equip->equipment_status;
        $equip->mechanical_status = $request->mechanical_status !== null ? $request->mechanical_status : $equip->mechanical_status;

        $equip->save();

        return response()->json([
            'success' => 'An equipment has been update',
            'old data' => $oldData,
            'new data' => $equip,
        ]);
    }
}

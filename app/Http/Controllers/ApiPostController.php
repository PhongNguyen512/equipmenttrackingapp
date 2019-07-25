<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Equipment;
use App\EquipmentClass;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use DateTime;

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

        //This is for TESTING ONLY. Postman can't POST data with boolean. The following code is for convert string -> boolean
        if($request->equipment_status !== null){
            if($request->equipment_status === 'false' ){
                $request->equipment_status = filter_var($request->equipment_status, FILTER_VALIDATE_BOOLEAN);
            }else{
                $request->equipment_status = filter_var($request->equipment_status, FILTER_VALIDATE_BOOLEAN);
            }
        }
        
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
            'equipment_status' => $request->equipment_status !== null ? 
                                    $request->equipment_status === true ? 'AV' : 'DM'
                                    : 'AV',
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

        $data = json_decode($request->getContent(), true);

        //if POST data is nothing
        if( count($data) == 0 ){
            return response()->json([
                'error' => 'Updating data not found',
            ])->setStatusCode(400);
        }

        $data = (object)$data;

        //This is for TESTING ONLY. Postman can't POST data with boolean. The following code is for convert string -> boolean
        // if($data->equipment_status !== null){
        //     if($data->equipment_status === 'false' ){
        //         $data->equipment_status = filter_var($data->equipment_status, FILTER_VALIDATE_BOOLEAN);
        //     }else{
        //         $data->equipment_status = filter_var($data->equipment_status, FILTER_VALIDATE_BOOLEAN);
        //     }
        // }

        $validator = Validator::make((array)$data, [
            'unit' => ['min:3', Rule::unique('equipments', 'unit')->ignore($equip->id) ],
            'ltd_smu' => ['numeric'],
        ]);

        if($data->ltd_smu < $equip->ltd_smu){
            return response()->json([
                'error' => "SMU is invalid. Please contact coordinator or admin.",
            ])->setStatusCode(400);
        }

        if($validator->fails()){
            return response()->json([
                'error' => $validator->messages()->first(),
            ])->setStatusCode(400);
        }

        $oldData = Equipment::find($equip->id);

        $equip->unit = isset($data->unit) ? $data->unit : $equip->unit;
        $equip->description = isset($data->description) ? $data->description : $equip->description;
        $equip->ltd_smu = isset($data->ltd_smu) ? $data->ltd_smu : $equip->ltd_smu;
        $equip->owning_status = isset($data->owning_status) ? $data->owning_status : $equip->owning_status;

        $equip->equipment_status = isset($data->equipment_status) ? 
                                    $data->equipment_status === true ? 'AV' : 'DM'
                                    : $equip->equipment_status;

        // $equip->mechanical_status = $data->mechanical_status !== null ? $data->mechanical_status : $equip->mechanical_status;
        $equip->mechanical_status = isset($data->mechanical_status) ? $data->mechanical_status : $equip->mechanical_status;

        $equip->save();

        
        $this->logUpdateEquip($equip, $request->header('Authorization') );

        // temperary change for app usage
        $equip->equipment_status = $equip->equipment_status === 'AV' ? true : false;

        return response()->json([
            'success' => 'An equipment has been update',
            'old data' => $oldData,
            'new data' => $equip,
        ]);
    }

    public function deleteSite(Site $site){
        if( count($site->EquipmentClassList) > 0 ){
            return response()->json([
                'error' => 'You can\'t delete at this moment. ',
            ], 400);
        }

        $site->delete();

        return response()->json([
            'success' => 'A Site has been deleted',
        ]);
    }

    public function deleteEquipmentClass(EquipmentClass $equipClass){
        if( count($equipClass->EquipmentList) > 0 ){
            return response()->json([
                'error' => 'You can\'t delete at this moment. ',
            ], 400);
        }

        $equipClass->delete();

        return response()->json([
            'success' => 'An Equipment Class has been deleted',
        ]);
    }

    public function deleteEquip(Equipment $equip){
        $equip->delete();

        return response()->json([
            'success' => 'An Equipment has been deleted',
        ]);
    }

    public function logUpdateEquip($data, $token){

        switch($data->equipment_status){
            case 'AV':
                $this->logAV();
                break;
            case 'DM':
                $this->logDM();
                break;
            default:
                break;
        }



        // $equipmentId = $data->id;

        // //Convert the updated time
        // $datetime = new DateTime($data->updated_at);

        // if( $datetime->format('H') > 19 && $datetime->format('H') < 7 )
        //     $shift = "Day";
        // else
        //     $shift = "Night";

        // $smu = $data->ltd_smu;

        // $unit = $data->unit;

        // $equipClass = $data->EquipmentClassList()->first();

        // $class = $equipClass->billing_rate.' '.$equipClass->equipment_class_name;

        // $summary = $equipClass->billing_rate;

        // $http = new \GuzzleHttp\Client;

        // $user = json_decode((string) $http->request('GET', url('/').'/api/auth/user', [
        //     'headers' => [
        //         'Accept' => 'application/json',
        //         'Authorization' => $token,
        //     ],
        // ])->getBody(), true);

        // //location will be prompt by foremen
        // // $location = 

       
        // dd($data);
        
    }

    public function logDM(){

    }

    public function logAV(){
        
    }


}

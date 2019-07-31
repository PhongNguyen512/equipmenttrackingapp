<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Site;
use App\Equipment;
use App\EquipmentClass;
use App\EquipUpdateLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use DateTime;
use App\Exceptions\Handler;
use Illuminate\Support\Facades\DB;
use Edujugon\PushNotification\PushNotification;

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
            ], 401);
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
            ], 401);
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
            ], 401);
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
            ], 401);
        }

         //validate input value
         $validator = Validator::make($request->all(), [
            'site_name' => ['required', Rule::unique('sites', 'site_name')->ignore($site->id)],
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => $validator->messages()->first(),
            ], 401);
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
            ], 401);
        }

        //validate input value
        $validator = Validator::make($request->all(), [
            'equipment_class_name' => [Rule::unique('equipment_classes', 'equipment_class_name')->ignore($equipClass->id)],
        ]);

        if($validator->fails()){
            return response()->json([
                'error' => $validator->messages()->first(),
            ], 401);
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

        $equip->updated_at = now();

        $equip->save();

        //Send notification only for admin and coordinator
        $users = DB::table('users')
            ->where('user_role_id', '=', '1')
            ->orWhere('user_role_id', '=', '2')
            ->get();

        //go to each filtered user and push notification
        foreach($users as $user){
            $this->sendNotification($user->app_token, $equip);
        }

        if($oldData->equipment_status !== $equip->equipment_status)
            $this->logUpdateEquip($equip, $request->header('Authorization'), $data );

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

    public function logUpdateEquip($equip, $token, $data){

        switch($equip->equipment_status){
            case 'AV':
                $this->logAV($equip, $token, $data);
                break;
            case 'DM':
                $this->logDM($equip, $token, $data);
                break;
            default:
                break;
        }
        
    }

    public function logDM($equip, $token, $data){

        if( $equip->updated_at->format('H') < 19 && $equip->updated_at->format('H') > 7 )
            $shift = "Day";
        else
            $shift = "Night";

        if( $equip->updated_at->format('H') >= 7 && $equip->updated_at->format('H') <= 8 )
            $startStatus = "DM";
        else
            $startStatus = "AV";

        $equipClass = $equip->EquipmentClassList()->first();

        //get user information based on token
        $http = new \GuzzleHttp\Client;
        $user = (object)json_decode((string) $http->request('GET', url('/').'/api/auth/user', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => $token,
            ],
        ])->getBody(), true);

        EquipUpdateLog::create([
            'date' => $equip->updated_at->format('d M'),
            'shift' => $shift,
            'smu' => $equip->ltd_smu,
            'unit' => $equip->unit,
            'class' => $equipClass->billing_rate.' '.$equipClass->equipment_class_name,
            'summary' => $equipClass->billing_rate,
            'start_of_shift_status' => $startStatus,
            'current_status' => 'DM',
            'comments' => ( isset($data->comments) ? $data->comments : '' ),
            'down_at' => now()->format("H:i"),
            'time_entry' => now()->format("H:i"),
            'location' => ( isset($data->location) ? $data->location : '' ),
            'updated_at' => now(),
            'equipment_id' => $equip->id,
            'user_id' => $user->id
        ]);
    }

    private function logAV($equip, $token, $data){

        try{
            $logEntry = DB::table('equip_update_logs')
                        ->where('date', '=', now()->format('d M'))
                        ->where('equipment_id', '=', $equip->id)
                        ->latest()
                        ->first();
            
            if($logEntry !== null){
                //get the data with EquipUpdateLog type for using save() later
                $logEntry = EquipUpdateLog::find($logEntry->id);
                $this->updateExistLogEntry($equip, $logEntry, $data);
            }
            else{
                $this->newLogEntryAV($equip, $token, $data);
            }
            
        }catch(Exception $e){
        
        }
    }

    private function updateExistLogEntry($equip, $logEntry, $data){

        $logEntry->smu = $equip->ltd_smu;
        $logEntry->current_status = 'AV';
        $logEntry->up_at = now()->format("H:i");
        $logEntry->time_entry = now()->format("H:i");
        $logEntry->updated_at = now();

        //get the difference in minutes
        $time_diff = date_diff( date_create($logEntry->down_at), date_create($logEntry->up_at) );
        $downTime = round(($time_diff->h * 60 + $time_diff->i) / 60, 2) ;

        $logEntry->down_hrs = $downTime;

        //calculate parked_hrs
        $logEntry->parked_hrs = 12 - $logEntry->down_hrs - ($logEntry->operated_hrs !== null ? $logEntry->operated_hrs : 0);

        if( isset($data->comments) )
            $logEntry->comments = $data->comments;
        
        if( isset($data->location) )
            $logEntry->location = $data->location;
        
        $logEntry->save();
    }

    private function newLogEntryAV($equip, $token, $data){

        if( $equip->updated_at->format('H') < 19 && $equip->updated_at->format('H') > 7 )
            $shift = "Day";
        else
            $shift = "Night";

        $equipClass = $equip->EquipmentClassList()->first();

        //get user information based on token
        $http = new \GuzzleHttp\Client;
        $user = (object)json_decode((string) $http->request('GET', url('/').'/api/auth/user', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => $token,
            ],
        ])->getBody(), true);

        $downAt = date("H:i", strtotime("07:00am"));
        $upAt = now()->format("H:i");

        //get the difference in minutes
        $time_diff = date_diff( date_create($downAt), date_create($upAt) );
        $downTime = round(($time_diff->h * 60 + $time_diff->i) / 60, 2) ;
        $parkTime = 12 - $downTime;

        EquipUpdateLog::create([
            'date' => $equip->updated_at->format('d M'),
            'shift' => $shift,
            'smu' => $equip->ltd_smu,
            'unit' => $equip->unit,
            'class' => $equipClass->billing_rate.' '.$equipClass->equipment_class_name,
            'summary' => $equipClass->billing_rate,
            'parked_hrs' => $parkTime,
            'down_hrs' => $downTime,
            'start_of_shift_status' => "DM",
            'current_status' => 'AV',
            'down_at' => $downAt,
            'up_at' => $upAt,
            'time_entry' => now()->format("H:i"),
            'updated_at' => now(),
            'equipment_id' => $equip->id,
            'user_id' => $user->id,
            'comments' => ( isset($data->comments) ? $data->comments : '' ),
            'location' => ( isset($data->location) ? $data->location : '' ),
        ]);
    }

    private function sendNotification($appToken, $equip){

        $equipId = $equip->id;
        $equipmentClassId = $equip->EquipmentClassList()->get()->first()->id;
        $siteId = EquipmentClass::find($equipmentClassId)->SiteList()->get()->first()->id;

        $push = new PushNotification('fcm');
        $push->setMessage([
                'notification' => [
                    'title' => 'Equipment Update Detected',
                    'body' => $equip->unit.' has been updated',
                    'sound' => true,
                    'click_action' => 'FCM_PLUGIN_ACTIVITY'
                    ],
                'data' => [
                    'action' => 'openEquipment',
                    'siteId' => $siteId,
                    'equipmentClassId' => $equipmentClassId,
                    'equipmentId' => $equipId
                ]
            ])
            ->setApiKey('AIzaSyAmQCoLOKOfz7AY8J22RP_q43fO7TfLKxM')
            ->setDevicesToken($appToken)
            ->send();
    }
}

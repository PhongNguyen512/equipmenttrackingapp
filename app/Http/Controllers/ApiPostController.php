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
use Carbon\Carbon;

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

    ////////////
    // require => site_name
    ///////////
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

        //convert to object to access data easier
        $data = (object)$data;

        $validator = Validator::make((array)$data, [
            'unit' => ['min:3', Rule::unique('equipments', 'unit')->ignore($equip->id) ],
            'ltd_smu' => ['numeric'],
        ]);

        //not allow to let forement change smu, except admin, coordinator
        if($request->user()->user_role_id === 3){
            if($data->ltd_smu < $equip->ltd_smu){
                return response()->json([
                    'error' => "SMU is invalid. Please contact coordinator or admin.",
                ])->setStatusCode(400);
            }
        }

        if($validator->fails()){
            return response()->json([
                'error' => $validator->messages()->first(),
            ])->setStatusCode(400);
        }

        //original data of equipment before updating
        $oldData = Equipment::find($equip->id);

        $equip->unit = isset($data->unit) ? $data->unit : $equip->unit;
        $equip->description = isset($data->description) ? $data->description : $equip->description;

        if($data->ltd_smu !== $equip->ltd_smu){
            $equip->last_entry_ltd_smu = $equip->ltd_smu;
        }

        $equip->ltd_smu = isset($data->ltd_smu) ? $data->ltd_smu : $equip->ltd_smu;
        $equip->owning_status = isset($data->owning_status) ? $data->owning_status : $equip->owning_status;

        //the value of equipment_status receive is true | false
        //because of the front-end guy
        //equipment_status in DB must be AV | DM
        $equip->equipment_status = isset($data->equipment_status) ? 
                                    $data->equipment_status === true ? 'AV' : 'DM'
                                    : $equip->equipment_status;

        $equip->mechanical_status = isset($data->mechanical_status) ? $data->mechanical_status : $equip->mechanical_status;

        //Must have this for doing log later
        $equip->updated_at = now();

        //if lat or lng is empty return error
        //gps need lat and lng
        if( isset($data->lat) && isset($data->lng) ){
            $equip->lat = $data->lat;
            $equip->lng = $data->lng;
        }elseif( !isset($data->lat) && isset($data->lng) 
            || !isset($data->lng) && isset($data->lat) ){
            return response()->json([
                'message' => 'Something wrong with location coordinates'
            ], 400);
        }else{
            $equip->lat = 0;
            $equip->lng = 0;
        }

        $equip->additional_detail = isset($data->additional_detail) ? $data->additional_detail : null;

        $equip->save();

        //Send notification only for admin and coordinator
        $users = DB::table('users')
            ->where('user_role_id', '=', '1')
            ->orWhere('user_role_id', '=', '2')
            ->get();

        //go to each filtered user and push notification
        foreach($users as $user){
            $this->sendNotification($user, $equip);
        }
  
        //system will log the equipment when status change av <-> dm
        if($oldData->equipment_status !== $equip->equipment_status)
            $this->logUpdateEquip($equip, $request->header('Authorization'), $data );

        // temperary change for app usage
        $equip->equipment_status = $equip->equipment_status === 'AV' ? true : false;

        //get est date of repair if equip is DM
        if(!$equip->equipment_status){
            $logEntry = DB::table('equip_update_logs')
                    ->where('unit', '=', $equip->unit)
                    ->latest()
                    ->first();
                    
            if($logEntry !== null)
                $equip->est_date_of_repair = $logEntry->est_date_of_repair;
        }else
            $equip->est_date_of_repair = null;

        return response()->json([
            'success' => 'An equipment has been updated',
            'old data' => $oldData,
            'new data' => $equip,
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

        //check if equipment is DM at ~7-8 => start shift will be DM
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
            'date' => $equip->updated_at->format('Y-m-d'),
            'shift' => $shift,
            'smu' => $equip->ltd_smu,
            'unit' => $equip->unit,
            'equipment_class' => $equipClass->billing_rate.' '.$equipClass->equipment_class_name,
            'summary' => $equipClass->billing_rate,
            'start_of_shift_status' => $startStatus,
            'current_status' => 'DM',
            'comments' => ( isset($data->comments) ? $data->comments : '' ),
            'down_at' => now()->format("H:i"),
            'time_entry' => now()->format("H:i"),
            'updated_at' => now(),
            'equipment_id' => $equip->id,
            'user_id' => $user->id,
            'lat' => ( isset($data->lat) ? $data->lat : 0 ),
            'lng' => ( isset($data->lng) ? $data->lng : 0 ),
            'est_date_of_repair' => ( isset($data->est_date_of_repair) ? date('Y-m-d', strtotime($data->est_date_of_repair) ) : null ),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function logAV($equip, $token, $data){

        try{
            $logEntry = DB::table('equip_update_logs')
                        ->where('date', '=', now()->format('Y-m-d'))
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
            //Not sure try catch work???
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

        if( isset($data->lat) && isset($data->lng) ){
            $logEntry->lat = $data->lat;
            $logEntry->lng = $data->lng;
        }
        
        $logEntry->updated_at = now();

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
            'date' => $equip->updated_at->format('Y-m-d'),
            'shift' => $shift,
            'smu' => $equip->ltd_smu,
            'unit' => $equip->unit,
            'equipment_class' => $equipClass->billing_rate.' '.$equipClass->equipment_class_name,
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
            'lat' => ( isset($data->lat) ? $data->lat : 0 ),
            'lng' => ( isset($data->lng) ? $data->lng : 0 ),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function sendNotification($appToken, $equip){

        $equipId = $equip->id;
        $equipmentClassId = $equip->EquipmentClassList()->get()->first()->id;
        $siteId = EquipmentClass::find($equipmentClassId)->SiteList()->get()->first()->id;

        $push = new PushNotification('fcm');
        if( isset($appToken->app_token) ){
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
                ->setDevicesToken($appToken->app_token)
                ->send();
        }else if( isset($appToken->pwa_token) ){
            //Not using the pwa_token
            //just for testing, not for the actual app
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
            ->setDevicesToken($appToken->pwa_token)
            ->send();
        }
    }

    public function getEntryLog(Request $request){
        $flag1Date = $flag2Date = false;
        if( isset($request->fromDate) && isset($request->toDate) ){
            $fromDate = $request->fromDate;
            $toDate = $request->toDate;
            $flag2Date = true;
        }
        elseif( isset($request->fromDate) ){
            $date = $request->fromDate;
            $flag1Date = true;
        }
        elseif( isset($request->toDate) ){
            $date = $request->toDate;
            $flag1Date = true;
        }

        if( $flag2Date ){
            //conver the time search input into
            $fromDate = date('Y-m-d', strtotime($fromDate) );
            // $toDate = date('Y-m-d', strtotime($toDate) + (3600 * 24) );
            $toDate = date('Y-m-d', strtotime($toDate) );

            $logEntry = DB::table('equip_update_logs')
                        ->whereBetween('created_at', [$fromDate, $toDate])->get();
        }elseif( $flag1Date ){
            $logEntry = DB::table('equip_update_logs')
                        ->where('date', '=', $date)->get();
        }

        return response()->json($logEntry); 
    }

    public function updateLogEntry(Request $request){       
 
        if( !isset($request->id) || $request->id === null )
            return response()->json([
                'message' => 'Log Entry Not Found',
            ], 404);

        //get data of the entry log which is updating
        $logEntry = DB::table('equip_update_logs')
                    ->where('id', $request->id)
                    ->get()->first();

        if($logEntry === null){
            return response()->json([
                'message' => 'Invalid Log Entry'
            ], 404);
        }

        //get all of log entries of same equipment at same date
        //except itself
        $arrayDownUpTime = json_decode(DB::table('equip_update_logs')
                    ->select('down_at', 'up_at')
                    ->where('date', '=', $logEntry->date)
                    ->where('unit', '=', $logEntry->unit)
                    ->where('id', '<>', $logEntry->id)
                    ->get(), true);

        //convert input search time for checking later       
        $down_at = Carbon::parse($request->down_at)->format('H:i:s');
        $up_at = $request->up_at !== null ? Carbon::parse($request->up_at)->format('H:i:s') : null;

        //only check if the equipment have more than 1 entry log of that day (except itself)
        if( sizeof($arrayDownUpTime) > 1 ){
            //checking input search time with down-up time in database            
            foreach($arrayDownUpTime as $time){
                $time = (object)$time;

                if($time->up_at === null)
                    continue;

                //check the current input timeframe with existed timeframe in DB
                if( $up_at > Carbon::parse($time->down_at)->format('H:i:s') && $up_at < Carbon::parse($time->up_at)->format('H:i:s') ||
                    $down_at > Carbon::parse($time->down_at)->format('H:i:s') && $down_at < Carbon::parse($time->up_at)->format('H:i:s') ||
                    Carbon::parse($time->down_at)->format('H:i:s') > $down_at && Carbon::parse($time->down_at)->format('H:i:s') < $up_at &&
                    Carbon::parse($time->up_at)->format('H:i:s') > $down_at && Carbon::parse($time->up_at)->format('H:i:s') < $up_at ){
                        return response()->json([
                            'error' => "The up or down time is inside timeframe of another log entry. Please check.",
                        ])->setStatusCode(400);
                }
            }    
        }     
        
        //if the log entry is already DM and change to AV
        //its will have up_at time => calculate park time
        if($up_at !== null){
            //get the difference in minutes
            $time_diff = date_diff( date_create( $down_at), date_create( $up_at) );
            $downTime = round(($time_diff->h * 60 + $time_diff->i) / 60, 2) ;
            $parkTime = 12 - $downTime;
        }

        //check if input down_at is ~7-8
        //system will force change the start shift status to DM
        $start_of_shift = '';
        if( isset($request->start_of_shift) ){
            if( Carbon::parse($request->down_at)->format('H') >= 7 && Carbon::parse($request->down_at)->format('H') < 9 )
                $start_of_shift = "DM";
            else
                $start_of_shift = $request->start_of_shift;
        }

        $current_status = '';
        if($up_at !== null && $down_at !== null){
            $current_status = 'AV';
        }elseif($down_at !== null & $up_at === null){
            $current_status = 'DM';
        }

        DB::table('equip_update_logs')
            ->where('id', $logEntry->id)
            ->update([
                'shift' => $request->shift,
                'smu' => $request->smu,
                'start_of_shift_status' => $start_of_shift,
                'comments' => $request->comments,
                'current_status' => $current_status,
                'down_at' =>  $down_at,
                'up_at' => $up_at,
                'time_entry' => Carbon::now()->format('H:i'),
                'user_id' => $request->user_id,
                'parked_hrs' => isset($parkTime) ? $parkTime : 12.00,
                'down_hrs' => isset($downTime) ? $downTime : 0.00,
                'est_date_of_repair' => isset($request->est_date_of_repair) ? $request->est_date_of_repair : null
            ]);

        return response()->json([
            'message' => 'Log Entry Updated',
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

    public function test(Request $request){
        dd( "hwllo", $request->user()->user_role_id );
    }
}
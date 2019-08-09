<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Equipment;
use App\EquipmentClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ApiReportController extends Controller
{
    /////////
    // Get Report 
    ////////
    public function getReport(Site $site){
        
        if(Cache::has('cacheReport')){
            $object = Cache::get('cacheReport');
            $object->time = now()->format('H:i');
        }
        else{
            
            $object = new \stdClass();
            $object->id = $site->id;
            $object->site_name = $site->site_name;
            $object->date = now()->format('M d, Y');
            $object->time = now()->format('H:i');
            $object->equipment_class_list = $this->getEquipClass($site->EquipmentClassList()->get()); 

            Cache::put('cacheReport', $object, 600);
        }

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

    public function updateReport(Request $request){ 
        
        $logEntryID = DB::table('equip_update_logs')
                ->select('id')
                ->where('unit', '=', $request->unit)
                ->latest()
                ->first()->id;

        $equipment = DB::table('equipments')
                ->where('unit', '=', $request->unit)
                ->first();

        $this->updateReportCache($request, $equipment);

        if( isset($request->est_date_of_repair) && $request->est_date_of_repair !== null ){
            if($equipment->equipment_status === 'DM')
                DB::table('equip_update_logs')
                    ->where('id', $logEntryID)
                    ->update([
                        'est_date_of_repair' => $request->est_date_of_repair
                    ]);

        }
        if( isset($request->note) && $request->note !== null ){
            DB::table('equip_update_logs')
                ->where('id', $logEntryID)
                ->update([
                    'comments' => $request->note
                ]);
        }
        if( isset($request->additional_detail) && $request->additional_detail !== null ){

            DB::table('equipments')
                ->where('id', $equipment->id)
                ->update([
                    'additional_detail' => $request->additional_detail
            ]);
        }

        return response()->json([
            'message' => 'Data of Unit '.$request->unit.' has been updated.'
        ]);
    }

    //when convert back to cache data.
    //original => [] of obj
    //thing got right now => object of []
    //for now it good to go
    //cause when get report everything will be json
    public function updateReportCache($request, $equipment){
        //convert object to string, convert again string to array
        $convertedArray = json_decode(json_encode(Cache::get('cacheReport')->equipment_class_list), true);

        //get data of cache
        $data = Cache::get('cacheReport');

        $equipmentListArray = array_column($convertedArray, 'equipment_list');
        $size = sizeof($equipmentListArray);

        for($i = 0 ; $i < $size ; $i++){
            $indexD2 = array_search($request->unit, array_column( $equipmentListArray[$i] , 'unit') );
            if($indexD2){
                $indexD1 = $i;
                break;
            }                
        }

        //assign update data 
        if( isset($request->est_date_of_repair) && $request->est_date_of_repair !== null ){
            if($equipment->equipment_status === 'DM')    
            $data->equipment_class_list[$indexD1]->equipment_list[$indexD2]->est_date_of_repair = $request->est_date_of_repair;
        }
        if( isset($request->note) && $request->note !== null ){
            $data->equipment_class_list[$indexD1]->equipment_list[$indexD2]->note = $request->note;
        }
        if( isset($request->additional_detail) && $request->additional_detail !== null ){
            $data->equipment_class_list[$indexD1]->equipment_list[$indexD2]->additional_detail = $request->additional_detail;
        }
        Cache::forget('cacheReport');
        Cache::put('cacheReport', $data, 600);
    }

    public function test(){
        dd( Cache::get('cacheReport') );
    }

}

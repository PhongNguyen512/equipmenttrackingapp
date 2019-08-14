<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Equipment;
use App\EquipmentClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Illuminate\Support\Facades\Mail;
use App\Mail\sendReport;
use Illuminate\Support\Facades\File;

class ApiReportController extends Controller
{
    /////////
    // Get Report 
    ////////
    public function getReport(Site $site){

        //check if report data exist in cache
        if(Cache::has('cacheReport')){
            $object = Cache::get('cacheReport');
    
            //check if request of different report
            if($object->id !== $site->id )
                $object = $this->cacheReportData($site);
            else
                $object->time = now()->format('H:i');
        }
        else{
            //generate report and save to cache
            $object = $this->cacheReportData($site);
        }

        return response()->json($object);
    }

    private function cacheReportData($site){
        $object = new \stdClass();
        $object->id = $site->id;
        $object->site_name = $site->site_name;
        $object->date = now()->format('M d, Y');
        $object->time = now()->format('H:i');
        $object->equipment_class_list = $this->getEquipClass($site->EquipmentClassList()->get()); 

        Cache::put('cacheReport', $object, 600);

        return $object;
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

            //find the latest log entry of the current equipment
            //to get all of updating stuff
            $logEntry = DB::table('equip_update_logs')
                        ->where('unit', '=', $d->unit)
                        ->latest()
                        ->first();

            if($logEntry !== null){
                if($d->equipment_status === 'DM')
                    $object->est_date_of_repair = $logEntry->est_date_of_repair;
                else
                    $object->est_date_of_repair =  null;

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
        
        //get the latest log entry incase there are multiple log entry for 1 equipment
        $logEntryID = DB::table('equip_update_logs')
                ->select('id')
                ->where('unit', '=', $request->unit)
                ->latest()
                ->first()->id;

        $equipment = DB::table('equipments')
                ->where('unit', '=', $request->unit)
                ->first();

        //get the input data (request) update the cache of report
        $this->updateReportCache($request, $equipment);

        //After update cache report data
        //system update directly to multiple table in DB
        if( isset($request->est_date_of_repair) && $request->est_date_of_repair !== null ){
            if($equipment->equipment_status === 'DM')
                DB::table('equip_update_logs')
                    ->where('id', $logEntryID)
                    ->update([
                        'est_date_of_repair' => date('Y-m-d', strtotime($request->est_date_of_repair))
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

        //after convert cache data to array
        //need to find index of equipment we need to change
        //the equipment we looking for is deep down inside 1 array
        //that array inside another array
        for($i = 0 ; $i < $size ; $i++){
            //if we found an index of deep down equipment
            //it will return index
            //return false if nothing false
            $indexD2 = array_search($request->unit, array_column( $equipmentListArray[$i] , 'unit') );

            //after found deep down index of equipment
            //we got index of equipment_class contains it by for loop
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

    public function generateReportFile($site){

        // $object = new \stdClass();
        // $object->id = $site->id;
        // $object->site_name = $site->site_name;
        // $object->date = now()->format('M d, Y');
        // $object->time = now()->format('H:i');
        // $object->equipment_class_list = $this->getEquipClass($site->EquipmentClassList()->get()); 

        if(Cache::has('cacheReport')){
            $object = json_decode(json_encode(Cache::get('cacheReport')), true);
            $object = json_decode(json_encode($object), true);
            $reportData = [];
            
            $spreadsheet = new Spreadsheet();
            //just set active 1 time, dont need to rewrite again
            $sheet = $spreadsheet->getActiveSheet();

            //fix data for heading
            $headingTitle = ['Unit Number', 'Equipment Status', 'Est Date of Repair', 'Note', 'Additional Detail'];
       
            foreach( $object['equipment_class_list'] as $equipment_class ){
                foreach($equipment_class['equipment_list'] as $equipment){
                    array_push($reportData, $equipment);
                }
            }

            //style
            $styleHeading = [
                'font' => [
                    'bold' => true,
                    'size' => 16
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FF87CEFA',
                    ]
                ],
            ];

            $styleSiteName = [
                'font' => [
                    'bold' => true,
                    'size' => 20
                ],
                'alignment' => [
                    'setVertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                ]
            ];

            $styleEquipmentClass = [
                'font' => [
                    'bold' => true,
                    'size' => 14
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFC0C0C0',
                    ]
                ],
            ];

            $styleEquipment = [
                'font' => [
                    'bold' => true,
                    'size' => 12
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ]
            ];

            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(26);
            $sheet->getColumnDimension('D')->setWidth(26);
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->getColumnDimension('F')->setWidth(32);

            $sheet->getStyle('B2:D2')->getFont()->setSize(14);

            //Data
            $sheet->setCellValue('C1', $object['site_name'].' Equipment');
            $sheet->setCellValue('B2', $object['date']);
            $sheet->setCellValue('D2', now()->format('H:i'));
            $sheet->fromArray($headingTitle, Null, 'B4');

            $sheet->getStyle('B4:F4')->applyFromArray($styleHeading);
            $sheet->getStyle('C1')->applyFromArray($styleSiteName);

            $row = 5;
            foreach($object['equipment_class_list'] as $equipment_class){
                $sheet->setCellValue('B'.$row, $equipment_class['equipment_class_name']);
                $sheet->getStyle('B'.$row.':F'.$row)->applyFromArray($styleEquipmentClass);
                $row++;
                $count = 1;
                foreach($equipment_class['equipment_list'] as $equipment){
                    $sheet->setCellValue('A'.$row, $count++);
                    $sheet->fromArray($equipment, Null, 'B'.$row);
                    $sheet->getStyle('B'.$row.':F'.$row)->applyFromArray($styleEquipment);

                    if($equipment['equipment_status'] === 'DM')
                        $sheet->getStyle('C'.$row)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);

                    $row++;
                }
            }
            
            //file name of the report file
            $fileName = 'report_'.$object['site_name'].'_equipment_'.( date('Y-m-d', strtotime($object['date']) ) ).'.xlsx';
            $writer = new Xlsx($spreadsheet);
            $writer->save($fileName);

            $arrayAdditionalReportMail = [];
            array_push($arrayAdditionalReportMail, [$object['site_name'], $object['date'], $fileName]);

            //testing send email ( TESTING PURPOSE )
            if(Cache::has('cacheAdditionalReportMail')){
                Cache::forget('cacheAdditionalReportMail');
                Cache::put('cacheAdditionalReportMail', $arrayAdditionalReportMail, 600);
            }
            else{
                Cache::put('cacheAdditionalReportMail', $arrayAdditionalReportMail, 600);
            }

        }
        else{
            return response()->json([
                'error' => 'Report not found. Please generate report first.'
            ])->setStatusCode(400);
        }
    
    }

    public function sendReport(Site $site, Request $request){

        //generate excel file and save in local before sent via email
        $this->generateReportFile($site);
        
        //cache data about additional when sending email
        if(Cache::has('cacheAdditionalReportMail')){
            $data = Cache::get('cacheAdditionalReportMail')[0];
            $siteName = $data[0];
            $date = $data[1];
            $fileName = $data[2];
        }
        else{
            return response()->json([
                'message' => 'Something wrong with the additional detail for sending mail'
            ])->setStatusCode(400);
        }

        //get the email of current user and set as sender
        $sendFrom = $request->user()->email;

        if( !isset($request->sendTo) ){
            //send email to yourself
            Mail::to($sendFrom)->send(new sendReport($sendFrom, $siteName, $date, $fileName));
        }else{
            //send email for single or multiple email
            //For now, when mail with cc & bcc, mailtrap got 2 email at same time????
            if( isset($request->cc) && isset($request->bcc) )
                Mail::to($request->sendTo)->cc($request->cc)->bcc($request->bcc)->send(new sendReport($sendFrom, $siteName, $date, $fileName));
            elseif( isset($request->cc) )
                Mail::to($request->sendTo)->cc($request->cc)->send(new sendReport($sendFrom, $siteName, $date, $fileName));
            else
                Mail::to($request->sendTo)->send(new sendReport($sendFrom, $siteName, $date, $fileName));
        }
        
        //delete report file after sent email
        File::delete($fileName);

        return response()->json([
            'message' => 'Email was sent'
        ]);
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Equipment;
use App\EquipmentClass;
use Illuminate\Support\Facades\Validator;

class ApiPostController extends Controller
{
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

    public function newEquipmentClass(Request $request){

    }
}

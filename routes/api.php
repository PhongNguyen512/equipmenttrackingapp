<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//eta.test/api/allData
Route::get('/allData','ApiGetController@allData');

//eta.test/api/allSites
Route::get('/allSites','ApiGetController@allSites');

//eta.test/api/allEquipClasses
Route::get('/allEquipClasses','ApiGetController@allEquipClass');

//eta.test/api/allEquip
Route::get('/allEquip','ApiGetController@allEquip');

///////////////////////////////////////////////////////////////////
//eta.test/api/newSite
Route::post('/newSite','ApiPostController@newSite');

//eta.test/api/newEquipmentClass
Route::post('/newEquipmentClass','ApiPostController@newEquipmentClass');

//eta.test/api/newEquip
Route::post('/newEquip','ApiPostController@newEquip');


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

Route::group(['middleware' => ['cors', 'auth:api' ] ], function(){
    //eta.test/api/allData
    Route::get('/allData','ApiGetController@allData');

    //eta.test/api/allSites
    Route::get('/allSites','ApiGetController@allSites');

    //eta.test/api/allEquipClasses
    Route::get('/allEquipClasses','ApiGetController@allEquipClass');

    //eta.test/api/allEquip
    Route::get('/allEquip','ApiGetController@allEquip');

    //eta.test/api/allUser
    Route::get('/allUser', 'ApiGetController@allUser');

    ///////////////////////////////////////////////////////////////////
    //eta.test/api/newSite
    Route::post('/newSite','ApiPostController@newSite')->middleware(['scope:admin,coordinator']);

    //eta.test/api/newEquipmentClass
    Route::post('/newEquipmentClass','ApiPostController@newEquipmentClass')->middleware(['scope:admin,coordinator']);

    //eta.test/api/newEquip
    Route::post('/newEquip','ApiPostController@newEquip')->middleware(['scope:admin,coordinator']);

    ///////////////////////////////////////////////////////////////////
    //eta.test/api/updateSite/8
    Route::post('/updateSite/{site}','ApiPostController@updateSite')->middleware(['scope:admin,coordinator']);

    //eta.test/api/updateEquipmentClass/3
    Route::post('/updateEquipmentClass/{equipClass}','ApiPostController@updateEquipmentClass')->middleware(['scope:admin,coordinator']);

    //eta.test/api/updateEquip/10
    Route::post('/updateEquip/{equip}','ApiPostController@updateEquip');

    //eta.test/api/deleteSite/11
    Route::delete('/deleteSite/{site}', 'ApiPostController@deleteSite')->middleware(['scope:admin']);

    //eta.test/api/deleteEquipmentClass/7
    Route::delete('/deleteEquipmentClass/{equipClass}', 'ApiPostController@deleteEquipmentClass')->middleware(['scope:admin']);

    //eta.test/api/deleteEquip/8
    Route::delete('/deleteEquip/{equip}', 'ApiPostController@deleteEquip')->middleware(['scope:admin']);

    //eta.test/api/getEntryLog
    Route::post('/getEntryLog', 'ApiPostController@getEntryLog')->middleware(['scope:coordinator,admin']);

    //eta.test/api/updateLogEntry
    Route::post('/updateLogEntry', 'ApiPostController@updateLogEntry')->middleware(['scope:coordinator,admin']);

    /////////////////////////////////////////////////////////////////////    
    //eta.test/api/getReport/1
    Route::get('/getReport/{site}','ApiReportController@getReport')->middleware(['scope:coordinator,admin']);

    //eta.test/api/updateReport
    //update for each item in report
    Route::post('/updateReport', 'ApiReportController@updateReport')->middleware(['scope:coordinator,admin']);

});

//////////////////////////////////////
// Authentication
Route::group([ 'prefix' => 'auth', 'middleware' => 'cors' ], function () {
    //eta.test/api/auth/login
    Route::post('login', 'AuthController@login')->name('login');

    //eta.test/api/auth/refreshToken
    Route::post('refreshToken', 'AuthController@refreshToken');
    
    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        //eta.test/api/auth/logout
        Route::get('logout', 'AuthController@logout');

        //eta.test/api/auth/user
        Route::get('user', 'AuthController@user');

        //eta.test/api/auth/appToken
        Route::post('appToken', 'AuthController@getAppToken');

    });
});

// eta.test/api/test
Route::get('test', 'ApiReportController@test');
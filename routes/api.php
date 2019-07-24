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

Route::group(['middleware' => 'cors', 'middleware' => 'auth:api'], function(){
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
    Route::post('/newSite','ApiPostController@newSite')->middleware(['scopes:admin']);

    //eta.test/api/newEquipmentClass
    Route::post('/newEquipmentClass','ApiPostController@newEquipmentClass');

    //eta.test/api/newEquip
    Route::post('/newEquip','ApiPostController@newEquip');

    ///////////////////////////////////////////////////////////////////
    //eta.test/api/updateSite/8
    Route::post('/updateSite/{site}','ApiPostController@updateSite');

    //eta.test/api/updateEquipmentClass/3
    Route::post('/updateEquipmentClass/{equipClass}','ApiPostController@updateEquipmentClass');

    //eta.test/api/updateEquip/10
    Route::post('/updateEquip/{equip}','ApiPostController@updateEquip');

    //eta.test/api/deleteSite/11
    Route::delete('/deleteSite/{site}', 'ApiPostController@deleteSite');

    //eta.test/api/deleteEquipmentClass/7
    Route::delete('/deleteEquipmentClass/{equipClass}', 'ApiPostController@deleteEquipmentClass');

    //eta.test/api/deleteEquip/8
    Route::delete('/deleteEquip/{equip}', 'ApiPostController@deleteEquip');

   
});

//////////////////////////////////////
// Authentication
Route::group([
    'prefix' => 'auth',
    'middleware' => 'cors'
], function () {
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


    });
});
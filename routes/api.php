<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

$corsGroup = [
    'readOnly'  => 'cors:GET,OPTIONS',  // item yg read only cuman bsa GET sama OPTIONS
    'singleItem'=> 'cors:GET,PUT,DELETE,OPTIONS,PATCH', // single item bsa macem2
    'all'       => 'cors:*',    // klo bisa jgn pake ini ya
    'resourceGroup'  => 'cors:GET,POST,OPTIONS' // group bisa diinsert, dilihat, dicek
];

// Kayaknya bagusnya digroup per endpoints dah
// OPTIONS /* untuk menghandle preflight CORS request
Route::options('/{fuckers}', 'ApiController@options')
        ->where('fuckers', '.+')
        ->middleware('cors:GET,POST,PUT,DELETE,OPTIONS,PATCH,HEAD');

// SSO, USER-RELATED
// ====================================================
// show all user based on a certain parameter
Route::get('/sso', 'SSOUserCacheController@index')
->middleware($corsGroup['resourceGroup'], 'role');

// show sso user per id
Route::get('/sso/{id}', 'SSOUserCacheController@show')
->middleware($corsGroup['singleItem'], 'role');

// show all user with role 'tpp.kasi'
Route::get('/kasi', 'SSOUserCacheController@getKasi')
->middleware($corsGroup['resourceGroup'],'role');

// TPS API
// ====================================================
// index all TPS
Route::get('/tps', 'TPSController@index')
->middleware($corsGroup['resourceGroup'], 'role');

// show TPS by kode
Route::get('/tps/{kode}', 'TPSController@showByKode')
->middleware($corsGroup['singleItem'], 'role');

// store new TPS
Route::post('/tps', 'TPSController@store')
->middleware($corsGroup['resourceGroup'], 'role:CONSOLE');

// update existing TPS
Route::put('/tps/{id}', 'TPSController@update')
->middleware($corsGroup['singleItem'], 'role:CONSOLE');

// delete existing TPS
Route::delete('/tps/{id}', 'TPSController@destroy')
->middleware($corsGroup['singleItem'], 'role:CONSOLE');

// index all TPS that has AWB ready for penetapan
Route::get('/tps/{kode}/siap_penetapan', 'TPSController@indexAwbSiapPenetapan')
->middleware($corsGroup['resourceGroup'], 'role:PELAKSANA,CONSOLE');

// index all TPS that has AWB ready for rekam BAST
Route::get('/tps/{kode}/siap_rekam_bast', 'TPSController@indexAwbSiapRekamBAST')
->middleware($corsGroup['resourceGroup'], 'role:PELAKSANA,CONSOLE');

// ENTRY MANIFEST/AWB
// ====================================================
// index all awb, latest to oldest
Route::get('/awb', 'EntryManifestController@index')
->middleware($corsGroup['resourceGroup'],'role');

// index all awb, ready for gate in
Route::get('/awb/siap_gate_in', 'EntryManifestController@indexSiapGateIn')
->middleware($corsGroup['resourceGroup'], 'role');

// show awb per id
Route::get('/awb/{id}', 'EntryManifestController@show')
->middleware($corsGroup['singleItem'],'role');

// post new AWB data from excel file
Route::post('/awb', 'EntryManifestController@postFromExcel')
->middleware($corsGroup['resourceGroup'], 'role:PELAKSANA,CONSOLE');

// delete AWB by id
Route::delete('/awb/{id}', 'EntryManifestController@destroy')
->middleware($corsGroup['singleItem'], 'role:PELAKSANA,CONSOLE');

// EXCEL API
// ====================================================
// parse excel file for initial data input
Route::post('/excel/dataawal', 'ExcelController@importDataAwal')
->middleware($corsGroup['singleItem'], 'role:PELAKSANA,CONSOLE');

Route::get('/penetapan/{id}/excel', 'ExcelController@exportPenetapanDetail')
->middleware($corsGroup['singleItem'], 'role');

// PENETAPAN
// ====================================================
// index all penetapan
Route::get('/penetapan', 'PenetapanController@index')
->middleware($corsGroup['resourceGroup'],'role');

Route::get('/penetapan/{id}/awb', 'PenetapanController@indexAwb')
->middleware($corsGroup['resourceGroup'], 'role');

// store all AWB from particular TPS into penetapan
Route::put('/tps/{kode}/penetapan', 'PenetapanController@store')
->middleware($corsGroup['singleItem'], 'role:PELAKSANA,CONSOLE');

// GATE-IN
// ====================================================
// gate in an AWB based on id
Route::put('/awb/{id}/gate-in', 'EntryManifestController@storeGateIn')
->middleware($corsGroup['singleItem'], 'role:PELAKSANA,CONSOLE');

// BAST
// ====================================================
// index all bast
Route::get('/bast', 'BASTController@index')
->middleware($corsGroup['resourceGroup'], 'role');

// store all AWB from particular TPS into bast
Route::put('/tps/{kode}/bast', 'BASTController@store')
->middleware($corsGroup['resourceGroup'], 'role:PELAKSANA,CONSOLE');

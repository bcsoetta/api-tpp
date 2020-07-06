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

// TEST
// ====================================================
Route::get('/sso', 'SSOUserCacheController@index')
->middleware($corsGroup['resourceGroup'], 'role');

Route::get('/sso/{id}', 'SSOUserCacheController@show')
->middleware($corsGroup['singleItem'], 'role');

// TPS API
// ====================================================
Route::get('/tps', 'TPSController@index')
->middleware($corsGroup['resourceGroup'], 'role');

Route::get('/tps/{kode}', 'TPSController@showByKode')
->middleware($corsGroup['singleItem'], 'role');

Route::post('/tps', 'TPSController@store')
->middleware($corsGroup['resourceGroup'], 'role:CONSOLE');

Route::put('/tps/{id}', 'TPSController@update')
->middleware($corsGroup['singleItem'], 'role:CONSOLE');

Route::delete('/tps/{id}', 'TPSController@destroy')
->middleware($corsGroup['singleItem'], 'role:CONSOLE');

// ENTRY MANIFEST/AWB
// ====================================================
Route::get('/awb', 'EntryManifestController@index')
->middleware($corsGroup['resourceGroup'],'role');

Route::get('/awb/{id}', 'EntryManifestController@show')
->middleware($corsGroup['singleItem'],'role');

Route::post('/awb', 'EntryManifestController@postFromExcel')
->middleware($corsGroup['resourceGroup'], 'role:PELAKSANA,CONSOLE');

// EXCEL API
// ====================================================
Route::post('/excel/dataawal', 'ExcelController@importDataAwal')
->middleware($corsGroup['singleItem'], 'role:PELAKSANA,CONSOLE');

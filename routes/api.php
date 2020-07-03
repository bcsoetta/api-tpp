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
Route::get('/sso', 'SSOUserCacheController@index')
->middleware($corsGroup['resourceGroup'], 'role');

Route::get('/sso/{id}', 'SSOUserCacheController@show')
->middleware($corsGroup['singleItem'], 'role');

// TPS API
Route::get('/tps', 'TPSController@index')
->middleware($corsGroup['resourceGroup']);

Route::get('/tps/{kode}', 'TPSController@showByKode')
->middleware($corsGroup['singleItem']);

// ENTRY MANIFEST/AWB
Route::get('/awb', 'EntryManifestController@index')
->middleware($corsGroup['resourceGroup']);

Route::get('/awb/{id}', 'EntryManifestController@show')
->middleware($corsGroup['singleItem']);

// EXCEL API
Route::post('/excel/dataawal', 'ExcelController@importDataAwal')
->middleware($corsGroup['singleItem'], 'role:PELAKSANA,CONSOLE');
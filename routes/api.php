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

// DATA REFERENSI UMUM
// ====================================================
Route::get('/referensi-dokumen-penyelesaian', 'ReferensiController@indexReferensiDokumenPenyelesaian');

Route::post('/referensi-dokumen-penyelesaian', 'ReferensiController@storeReferensiDokumenPenyelesaian')
->middleware('role:CONSOLE');

Route::delete('/referensi-dokumen-penyelesaian/{id}', 'ReferensiController@destroyReferensiDokumenPenyelesaian')
->middleware('role:CONSOLE');

Route::put('/referensi-dokumen-penyelesaian/{id}', 'ReferensiController@updateReferensiDokumenPenyelesaian')
->middleware('role:CONSOLE');

// SETTING
// ====================================================
Route::get('/setting', 'SettingController@index')
->middleware('role');

// SSO, USER-RELATED
// ====================================================
// show all user based on a certain parameter
Route::get('/sso', 'SSOUserCacheController@index')
->middleware( 'role');

// show sso user per id
Route::get('/sso/{id}', 'SSOUserCacheController@show')
->middleware( 'role');

// show all user with role 'tpp.kasi'
Route::get('/kasi', 'SSOUserCacheController@getKasi')
->middleware('role');

// TPS API
// ====================================================
// index all TPS
Route::get('/tps', 'TPSController@index')
->middleware( 'role');

// show TPS by kode
Route::get('/tps/{kode}', 'TPSController@showByKode')
->middleware( 'role');

// store new TPS
Route::post('/tps', 'TPSController@store')
->middleware( 'role:CONSOLE');

// update existing TPS
Route::put('/tps/{id}', 'TPSController@update')
->middleware( 'role:CONSOLE');

// delete existing TPS
Route::delete('/tps/{id}', 'TPSController@destroy')
->middleware( 'role:CONSOLE');

// index all TPS that has AWB ready for penetapan
Route::get('/tps/{kode}/siap_penetapan', 'TPSController@indexAwbSiapPenetapan')
->middleware( 'role:PELAKSANA,CONSOLE');

// index all TPS that has AWB ready for rekam BAST
Route::get('/tps/{kode}/siap_rekam_bast', 'TPSController@indexAwbSiapRekamBAST')
->middleware( 'role:PELAKSANA,CONSOLE');

// ENTRY MANIFEST/AWB
// ====================================================
// index all awb, latest to oldest
Route::get('/awb', 'EntryManifestController@index')
->middleware('role');

// index all awb, ready for gate in
Route::get('/awb/siap_gate_in', 'EntryManifestController@indexSiapGateIn')
->middleware( 'role');

// show awb per id
Route::get('/awb/{id}', 'EntryManifestController@show')
->middleware('role');

// post new AWB data from excel file
Route::post('/awb', 'EntryManifestController@postFromExcel')
->middleware( 'role:PELAKSANA,CONSOLE');

// delete AWB by id
Route::delete('/awb/{id}', 'EntryManifestController@destroy')
->middleware( 'role:PELAKSANA,CONSOLE');

// rollback gate in
Route::delete('/awb/{id}/gate-in', 'EntryManifestController@rollbackGateIn')
->middleware('role:CONSOLE');

// rekam penyelesaian
Route::put('/awb/{id}/penyelesaian', 'EntryManifestController@storePenyelesaian')
->middleware('role:PELAKSANA,CONSOLE');

// patch ops
Route::patch('/awb/{id}', 'EntryManifestController@patch')
->middleware('role:PELAKSANA,CONSOLE');

// EXCEL API
// ====================================================
// parse excel file for initial data input
Route::post('/excel/dataawal', 'ExcelController@importDataAwal')
->middleware( 'role:PELAKSANA,CONSOLE');

// parse excel file for importing from kep bdn p2
Route::post('/excel/kepbdn', 'ExcelController@importKepBdn')
->middleware( 'role:PELAKSANA,CONSOLE');

// export excel file from Penetapan detail
Route::get('/penetapan/{id}/excel', 'ExcelController@exportPenetapanDetail')
->middleware( 'role');

// export excel file from BAST (btd onlehh)
Route::get('/bast/{id}/excel', 'ExcelController@exportBASTDetail')
->middleware( 'role');

// export excel file from BACacah
Route::get('/ba_cacah/{id}/excel', 'ExcelController@exportBACacahDetail')
->middleware('role');

// PENETAPAN
// ====================================================
// index all penetapan
Route::get('/penetapan', 'PenetapanController@index')
->middleware('role');

Route::get('/penetapan/{id}/awb', 'PenetapanController@indexAwb')
->middleware( 'role');

// store all AWB from particular TPS into penetapan
Route::put('/tps/{kode}/penetapan', 'PenetapanController@store')
->middleware( 'role:PELAKSANA,CONSOLE');

// edit surat penetapan
Route::patch('/penetapan/{id}', 'PenetapanController@updateSuratPenetapan')
->middleware('role:PELAKSANA,CONSOLE');

// GATE-IN
// ====================================================
// gate in an AWB based on id
Route::put('/awb/{id}/gate-in', 'EntryManifestController@storeGateIn')
->middleware( 'role:PELAKSANA,CONSOLE');

// GATE-OUT
// ====================================================
// gate out AWB based on id
Route::put('/awb/{id}/gate-out', 'EntryManifestController@storeGateOut')
->middleware('role:PELAKSANA,CONSOLE');

// BAST
// ====================================================
// index all bast
Route::get('/bast', 'BASTController@index')
->middleware( 'role');

Route::get('/bast/{id}/awb', 'BASTController@indexAwb')
->middleware( 'role');

// store all AWB from particular TPS into bast
Route::put('/tps/{kode}/bast', 'BASTController@store')
->middleware( 'role:PELAKSANA,CONSOLE');

// store specific AWB
Route::post('/bast', 'BASTController@storeSpecific')
->middleware( 'role:PELAKSANA,CONSOLE');

// PENCACAHAN
// ====================================================
// tambah data pencacahan (new or update)
Route::put('/awb/{id}/pencacahan', 'PencacahanController@createOrUpdate')
->middleware( 'role:PELAKSANA,CONSOLE');

// LAMPIRAN
// ====================================================
// upload lampiran
Route::post('/{doctype}/{id}/lampiran', 'UploadController@handleUpload')
->middleware( 'role');

// get all attachments
Route::get('/{doctype}/{id}/lampiran', 'UploadController@getAttachments')
->middleware( 'role');

// delete specific attachment
Route::delete('/lampiran/{id}', 'UploadController@deleteAttachment')
->middleware( 'role');

// BA CACAH
// ====================================================
// index all BACacah
Route::get('/ba_cacah', 'BACacahController@index')
->middleware( 'role');

// index all of AWB of a BACacah
Route::get('/ba_cacah/{id}/awb', 'BACacahController@indexAwb')
->middleware( 'role');

// show specific BACacah
Route::get('/ba_cacah/{id}', 'BACacahController@show')
->middleware( 'role');

// store
Route::post('/ba_cacah', 'BACacahController@store')
->middleware( 'role');

// PNBP
// ====================================================
// index all pnbp
Route::get('/pnbp', 'PNBPController@index')
->middleware('role');

// show one pnbp
Route::get('/pnbp/{id}', 'PNBPController@show')
->middleware('role');

// recalculate pnbp
Route::patch('/pnbp/{id}', 'PNBPController@patch')
->middleware('role');

// compute mockup
Route::get('/awb/{id}/pnbp-mockup', 'PNBPController@precalculatePNBP');

// post
Route::post('/pnbp', 'PNBPController@store')
->middleware('role:PELAKSANA,CONSOLE');


//====================================================================================================
// ENDPOINTS PDF
//====================================================================================================
// GET /pdf?doc=lembarhitungcd&id=2
Route::get('/pdf', 'PDFController@show')
        ;

//====================================================================================================
// ENDPOINTS RACK
//====================================================================================================
Route::get('/rack', 'RackController@index')
->middleware('role');

Route::get('/rack/{id}', 'RackController@show')
->middleware('role');

Route::get('/rack/{kode}', 'RackController@showByKode')
->middleware('role');

Route::post('/rack', 'RackController@store')
->middleware('role:CONSOLE');

Route::put('/rack/{id}', 'RackController@update')
->middleware('role:CONSOLE');

Route::delete('/rack/{id}', 'RackController@delete')
->middleware('role:CONSOLE');
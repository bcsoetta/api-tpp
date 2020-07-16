<?php

namespace App\Http\Controllers;

use App\Exports\ExportLampiranPenetapan;
use App\Imports\DataAwalImport;
use App\Penetapan;
use App\Transformers\EntryManifestTransformer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends ApiController
{
    // importing data awal
    public function importDataAwal(Request $r) {
        try {
            // is there file?
            $file = $r->file('file');

            if (!$file) throw new \Exception("No excel file provided!");

            // try to parse them
            $data = (new DataAwalImport)->importToModels($file);

            return $this->respondWithCollection($data, new EntryManifestTransformer);
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // export data penetapan
    public function exportPenetapanDetail(Request $r, $id) {
        // just do it
        try {
            $e = (new ExportLampiranPenetapan)->byId($id);

            $filename = preg_replace('/\//i', '-', $e->penetapan->nomor_lengkap_dok);

            return Excel::download($e, $filename . '.xlsx');
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\ExportLampiranBACacah;
use App\Exports\ExportLampiranBAST;
use App\Exports\ExportLampiranPenetapan;
use App\Imports\DataAwalImport;
use App\Imports\KepBDNImport;
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

    // importing kep bdn p2
    public function importKepBdn(Request $r) {
        try {
            // is there file?
            $file = $r->file('file');

            if (!$file) throw new \Exception("No excel file provided!");

            // try to parse them
            $data = (new KepBDNImport)->importToModels($file);

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

    // export detail BAST (btd only)
    public function exportBASTDetail(Request $r, $id) {
        // throw error for ex P2 stuffs
        try {
            $e = (new ExportLampiranBAST)->byId($id);

            $filename = preg_replace('/\//i', '-', $e->bast->nomor_lengkap_dok);

            return Excel::download($e, $filename . '.xlsx');
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // export lampiran BA Cacah
    public function exportBACacahDetail(Request $r, $id) {
        // throw error for ex P2 stuffs
        try {
            $e = (new ExportLampiranBACacah)->byId($id);

            $filename = preg_replace('/\//i', '-', $e->baCacah->nomor_lengkap_dok);

            return Excel::download($e, $filename . '.xlsx');
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}

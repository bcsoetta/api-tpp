<?php

namespace App\Http\Controllers;

use App\Imports\DataAwalImport;
use App\Transformers\EntryManifestTransformer;
use Illuminate\Http\Request;

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
}

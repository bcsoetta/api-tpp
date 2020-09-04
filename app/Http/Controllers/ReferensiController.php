<?php

namespace App\Http\Controllers;

use App\ReferensiDokumenPenyelesaian;
use App\Transformers\ReferensiDokumenPenyelesaianTransformer;
use Illuminate\Http\Request;

class ReferensiController extends ApiController
{
    // referensi dokumen penyelesaian
    public function indexReferensiDokumenPenyelesaian() {
        $data = ReferensiDokumenPenyelesaian::all();

        return $this->respondWithCollection($data, new ReferensiDokumenPenyelesaianTransformer);
    }
}

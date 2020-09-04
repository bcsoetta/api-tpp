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

    public function storeReferensiDokumenPenyelesaian(Request $r) {
        try {
            // create new something
            $nama = expectSomething($r->get('nama'), 'Jenis Dokumen');

            if (!strlen(trim($nama))) {
                throw new \Exception("Jenis Dokumen harus diisi");
            }

            $ref = ReferensiDokumenPenyelesaian::make([
                'nama' => $nama
            ]);

            $ref->save();

            return $this->respondWithArray([
                'id' => $ref->id
            ]);
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function destroyReferensiDokumenPenyelesaian($id) {
        try {
            $ref = ReferensiDokumenPenyelesaian::findOrFail($id);
            $ref->delete();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function updateReferensiDokumenPenyelesaian(Request $r, $id) {
        try {
            $ref = ReferensiDokumenPenyelesaian::findOrFail($id);
            $ref->nama = expectSomething($r->get('nama'), 'Jenis Dokumen');
            $ref->save();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}

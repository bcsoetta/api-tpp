<?php
namespace App\Transformers;

use App\Lokasi;
use League\Fractal\TransformerAbstract;

class LokasiTransformer extends TransformerAbstract {
    public function transform(Lokasi $l) {
        return [
            'id' => (int) $l->id,
            'kode' => $l->kode,
            'deskripsi' => $l->deskripsi
        ];
    }
}
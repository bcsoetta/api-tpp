<?php
namespace App\Transformers;

use App\ReferensiDokumenPenyelesaian;
use League\Fractal\TransformerAbstract;

class ReferensiDokumenPenyelesaianTransformer extends TransformerAbstract {
    public function transform(ReferensiDokumenPenyelesaian $r) {
        return [
            'id' => (int) $r->id,
            'nama' => $r->nama
        ];
    }
}
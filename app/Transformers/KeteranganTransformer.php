<?php
namespace App\Transformers;

use App\Keterangan;
use League\Fractal\TransformerAbstract;

class KeteranganTransformer extends TransformerAbstract {
    public function transform(Keterangan $k) {
        return [
            'id' => (int) $k->id,
            'keterangan' => $k->keterangan
        ];
    }
}
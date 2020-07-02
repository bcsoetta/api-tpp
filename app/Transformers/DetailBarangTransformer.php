<?php
namespace App\Transformers;

use App\DetailBarang;
use League\Fractal\TransformerAbstract;

class DetailBarangTransformer extends TransformerAbstract {
    public function transform(DetailBarang $d) {
        return [
            'id' => (int) $d->id,
            'uraian' => $d->uraian,
            'jumlah' => !is_null($d->jumlah) ? (float) $d->jumlah : null,
            'jenis' => $d->koli
        ];
    }
}
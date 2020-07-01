<?php
namespace App\Transformers;

use App\Satuan;
use League\Fractal\TransformerAbstract;

class SatuanTransformer extends TransformerAbstract {
    // list possible embeds/includes here
    protected $availableIncludes = [];

    // list defaultly loaded embed/includes
    protected $defaultIncludes = [];

    // simple transform for data penumpang
    public function transform(Satuan $s) {
        return [
            'id'     => $s->id,
            'kode'   => $s->kode,
            'uraian' => $s->uraian
        ];
    }
}

?>
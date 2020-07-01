<?php
namespace App\Transformers;

use App\Kemasan;
use League\Fractal\TransformerAbstract;

class KemasanTransformer extends TransformerAbstract {
    // list possible embeds/includes here
    protected $availableIncludes = [];

    // list defaultly loaded embed/includes
    protected $defaultIncludes = [];

    // simple transform for data penumpang
    public function transform(Kemasan $k) {
        return [
            'id'     => $k->id,
            'kode'   => $k->kode,
            'uraian' => $k->uraian
        ];
    }
}

?>
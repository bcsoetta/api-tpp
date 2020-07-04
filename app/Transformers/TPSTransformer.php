<?php
namespace App\Transformers;

use App\TPS;
use League\Fractal\TransformerAbstract;

class TPSTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'siap_penetapan'
    ];

    public function transform(TPS $t) {
        $data = [
            'id' => (int) $t->id,
            'kode' => $t->kode,
            'nama' => $t->nama,
            'alamat' => $t->alamat,
            'kode_kantor' => $t->kode_kantor
        ];

        if ($t->total) {
            $data['total'] = $t->total;
        }

        return $data;
    }

    public function includeSiapPenetapan(TPS $t) {
        $siap_penetapan = $t->entryManifest()->siapPenetapan()->get();
        return $this->collection($siap_penetapan, new EntryManifestTransformer);
    }
}
<?php
namespace App\Transformers;

use App\Penyelesaian;
use League\Fractal\TransformerAbstract;

class PenyelesaianTransformer extends TransformerAbstract {
    protected $availableIncludes = [
        'detail'
    ];

    public function transform(Penyelesaian $p) {
        return [
            'nomor_lengkap_dok' => (string) $p->nomor_lengkap_dok,
            'tgl_dok' => (string) $p->tgl_dok,
            'jenis_dokumen_id' => (int) $p->jenis_dokumen_id
        ];
    }

    public function includeDetail(Penyelesaian $p) {
        $d = $p->entryManifest;
        return $this->collection($d, new EntryManifestTransformer);
    }
}
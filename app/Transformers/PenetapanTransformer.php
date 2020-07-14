<?php
namespace App\Transformers;

use App\Penetapan;
use League\Fractal\TransformerAbstract;

class PenetapanTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'pejabat',
        'entry_manifest'
    ];

    protected $defaultIncludes = [
        'pejabat'
    ];

    public function transform(Penetapan $p) {
        return [
            'id' => (int) $p->id,
            'nomor_lengkap' => $p->nomor_lengkap_dok,
            'tgl_dok' => $p->tgl_dok
        ];
    }

    public function includePejabat(Penetapan $p) {
        $pejabat = $p->pejabat;
        if ($pejabat) {
            return $this->item($pejabat, new SSOUserCacheTransformer);
        }
    }

    public function includeEntryManifest(Penetapan $p) {
        return $this->collection($p->entryManifest, new EntryManifestTransformer);
    }
}
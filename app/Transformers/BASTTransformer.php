<?php
namespace App\Transformers;

use App\BAST;
use League\Fractal\TransformerAbstract;

class BASTTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'petugas',
        'entry_manifest'
    ];

    protected $defaultIncludes = [
        'petugas'
    ];

    public function transform(BAST $b) {
        return [
            'id' => (int) $b->id,
            'nomor_lengkap' => $b->nomor_lengkap_dok,
            'tgl_dok' => $b->tgl_dok,
            'ex_p2' => (bool) $b->ex_p2,
            'total_entry_manifest' => (int) $b->entryManifest()->count(),
        ];
    }

    public function includePetugas(BAST $p) {
        $petugas = $p->petugas;
        if ($petugas) {
            return $this->item($petugas, new SSOUserCacheTransformer);
        }
    }

    public function includeEntryManifest(BAST $p) {
        return $this->collection($p->entryManifest, new EntryManifestTransformer);
    }
}
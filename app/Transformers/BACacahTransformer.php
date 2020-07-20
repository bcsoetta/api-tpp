<?php
namespace App\Transformers;

use App\BACacah;
use League\Fractal\TransformerAbstract;

class BACacahTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'penetapan',
        'bast',
        'pelaksana',
        'pejabat',
        'entry_manifest'
    ];

    protected $defaultIncludes = [
        'pejabat'
    ];

    public function transform(BACacah $b) {
        return [
            'id' => (int) $b->id,
            'nomor_lengkap' => $b->nomor_lengkap_dok,
            'tgl_dok' => $b->tgl_dok,

            'nomor_st' => $b->nomor_st,
            'tgl_st' => $b->tgl_st,

            'total_pelaksana' => (int) $b->pelaksana()->count(),
            'total_entry_manifest' => (int) $b->entryManifest()->count()
        ];
    }

    public function includePejabat(BACacah $b) {
        return $this->item($b->pejabat, new SSOUserCacheTransformer);
    }

    public function includePelaksana(BACacah $b) {
        return $this->collection($b->pelaksana, new SSOUserCacheTransformer);
    }

    public function includeBast(BACacah $b) {
        return $this->collection($b->bast, new BASTTransformer);
    }

    public function includePenetapan(BACacah $b) {
        return $this->collection($b->penetapan, new PenetapanTransformer);
    }

    public function includeEntryManifest(BACacah $b) {
        return $this->collection($b->entryManifest, new EntryManifestTransformer);
    }
}
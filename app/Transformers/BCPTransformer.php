<?php
namespace App\Transformers;

use App\BCP;
use League\Fractal\TransformerAbstract;

class BCPTransformer extends TransformerAbstract {
    protected $availableIncludes = [
        'entryManifest'
    ];

    protected $defaultIncludes = [
    ];

    public function transform(BCP $b) {
        return [
            'id' => (int) $b->id,
            'no_dok' => (int) $b->no_dok,
            'nomor_lengkap' => $b->nomor_lengkap,
            'tgl_dok' => $b->tgl_dok,
            'jenis' => $b->jenis
        ];
    }

    public function includeEntryManifest(BCP $b) {
        $e = $b->entryManifest;
    }
}
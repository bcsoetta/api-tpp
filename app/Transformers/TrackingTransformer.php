<?php
namespace App\Transformers;

use App\Tracking;
use League\Fractal\TransformerAbstract;

class TrackingTransformer extends TransformerAbstract {
    protected $availableIncludes = [
        'trackable',
        'lokasi',
        'petugas'
    ];

    protected $defaultIncludes = [
        'lokasi',
        'petugas'
    ];

    public function transform(Tracking $t) {
        return [
            'id' => (int) $t->id,
            'created_at' => (string) $t->created_at,
            'updated_at' => (string) $t->updated_at
        ];
    }

    public function includeLokasi(Tracking $t) {
        return $this->item($t->lokasi, spawnTransformer($t->lokasi));
    }

    public function includePetugas(Tracking $t) {
        $p = $t->petugas;
        
        if ($p) {
            return $this->item($p, new SSOUserCacheTransformer);
        }
    }
}
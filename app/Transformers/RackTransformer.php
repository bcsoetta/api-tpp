<?php
namespace App\Transformers;

use App\Rack;
use League\Fractal\TransformerAbstract;

class RackTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'entry_manifest'
    ];

    public function transform(Rack $r) {
        return [
            'id' => (int) $r->id,
            'kode' => (string) $r->kode,
            'nama' => "Rak -={$r->kode}=- di Gudang TPP SH", 
            'x' => (float) $r->x,
            'y' => (float) $r->y,
            'w' => (float) $r->w,
            'h' => (float) $r->h,
            'rot' => (float) $r->rot,

            'total_awb' => (int) $r->entryManifest()->count(),

            'created_at' => (string) $r->created_at,
            'updated_at' => (string) $r->updated_at,
        ];
    }

    public function includeEntryManifest(Rack $r) {
        return $this->collection($r->entryManifest, new EntryManifestTransformer);
    }
}
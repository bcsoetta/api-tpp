<?php
namespace App\Transformers;

use App\Pencacahan;
use League\Fractal\TransformerAbstract;

class PencacahanTransformer extends TransformerAbstract {
    protected $availableIncludes = [
        'entryManifest',
        'barang'
    ];

    protected $defaultIncludes = [
        'barang'
    ];

    public function transform(Pencacahan $p) {
        return [
            'id' => (int) $p->id,

            'kondisi_barang' => $p->kondisi_barang,
            'peruntukan_awal' => $p->peruntukan_awal,

            'ringkasan_uraian_barang' => $p->ringkasan_uraian_barang,

            'is_locked' => $p->is_locked,

            'created_at' => (string) $p->created_at,
            'updated_at' => (string) $p->updated_at,
        ];
    }

    public function includeEntryManifest(Pencacahan $p) {
        return $this->item($p->entryManifest, new EntryManifestTransformer);
    }

    public function includeBarang(Pencacahan $p) {
        return $this->collection($p->detailBarang, new DetailBarangTransformer);
    }
}
<?php
namespace App\Transformers;

use App\Pencacahan;
use League\Fractal\TransformerAbstract;

class PencacahanTransformer extends TransformerAbstract {
    protected $availableIncludes = [
        'petugas',
        'entryManifest',
        'barang'
    ];

    protected $defaultIncludes = [
        'barang',
        'petugas'
    ];

    public function transform(Pencacahan $p) {
        return [
            'kondisi_barang' => $p->kondisi_barang,
            'peruntukan_awal' => $p->peruntukan_awal,
            'created_at' => (string) $p->created_at,
            'updated_at' => (string) $p->updated_at,
        ];
    }

    public function includePetugas(Pencacahan $p) {
        if ($p->petugas) {
            return $this->item($p->petugas, spawnTransformer($p->petugas));
        }
    }

    public function includeEntryManifest(Pencacahan $p) {
        return $this->item($p->entryManifest, new EntryManifestTransformer);
    }

    public function includeBarang(Pencacahan $p) {
        return $this->collection($p->detailBarang, new DetailBarangTransformer);
    }
}
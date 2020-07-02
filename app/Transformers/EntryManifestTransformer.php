<?php
namespace App\Transformers;

use App\EntryManifest;
use League\Fractal\TransformerAbstract;

class EntryManifestTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'tps',
        'bcp',
        'status',
        'barang',
        'tracking'
    ];

    protected $defaultIncludes = [
        'tps',
        'bcp',
        'status',
        'barang',
        'tracking'
    ];

    public function transform(EntryManifest $m) {
        return [
            'id' => (int) $m->id,
            'no_bc11' => (int) $m->no_bc11,
            'tgl_bc11' => $m->tgl_bc11,
            'pos' => (int) $m->pos,
            'subpos' => (int) $m->subpos,
            'subsubpos' => (int) $m->subsubpos,
            'kd_flight' => $m->kd_flight,
            'koli' => (float) $m->koli,
            'brutto' => (float) $m->brutto,
            'mawb' => $m->mawb,
            'hawb' => $m->hawb,
            // 'uraian' => $m->uraian,
            'nama_importir' => $m->nama_importir,
            'alamat_importir' => $m->alamat_importir
        ];
    }

    public function includeTps(EntryManifest $m) {
        $t = $m->tps;

        if ($t) {
            return $this->item($t, new TPSTransformer);
        }
    }

    public function includeBcp(EntryManifest $m) {
        $b = $m->bcp;

        if ($b) {
            return $this->item($b, new BCPTransformer);
        }
    }

    public function includeStatus(EntryManifest $m) {
        $s = $m->statusOrdered();

        return $this->collection($s, new StatusTransformer);
    }

    public function includeBarang(EntryManifest $m) {
        $b = $m->detailBarang;

        return $this->collection($b, new DetailBarangTransformer);
    }

    public function includeTracking(EntryManifest $m) {
        $t = $m->tracking()->latest()->orderBy('id', 'desc')->get();

        return $this->collection($t, new TrackingTransformer);
    }
}
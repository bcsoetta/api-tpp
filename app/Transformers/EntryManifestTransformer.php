<?php
namespace App\Transformers;

use App\EntryManifest;
use League\Fractal\TransformerAbstract;

class EntryManifestTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'keterangan',
        'tps',
        'bcp',
        'status',
        'barang',
        'tracking',
        'pencacahan'
    ];

    protected $defaultIncludes = [
        'keterangan',
        'tps',
        'bcp',
        'status',
        'barang',
        'tracking',
        'pencacahan'
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
            'alamat_importir' => $m->alamat_importir,

            'last_tracking' => $m->last_tracking->lokasi ?? null
        ];
    }

    public function includeKeterangan(EntryManifest $m) {
        $k = $m->keterangan;

        return $this->collection($k, new KeteranganTransformer);
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
        $s = $m->status;

        return $this->collection($s, new StatusTransformer);
    }

    public function includeBarang(EntryManifest $m) {
        $b = $m->detailBarang;

        return $this->collection($b, new DetailBarangTransformer);
    }

    public function includeTracking(EntryManifest $m) {
        $t = $m->tracking;

        return $this->collection($t, new TrackingTransformer);
    }

    public function includePencacahan(EntryManifest $m) {
        $p = $m->pencacahan;

        if ($p) {
            return $this->item($p, new PencacahanTransformer);
        }
    }
}
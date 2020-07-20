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
        'pencacahan',
        'last_tracking',
        'lampiran'
    ];

    protected $defaultIncludes = [
        'keterangan',
        'tps',
        'bcp',
        'status',
        'barang',
        'tracking',
        'pencacahan',
        'last_tracking',
        'lampiran'
    ];

    public function transform(EntryManifest $m) {

        return [
            'id' => (int) $m->id,
            'no_bc11' => (int) $m->no_bc11,
            'tgl_bc11' => $m->tgl_bc11,
            'pos' => (int) $m->pos,
            'subpos' => (int) $m->subpos,
            'subsubpos' => (int) $m->subsubpos,
            'pos_formatted' => $m->pos_formatted,
            'kd_flight' => $m->kd_flight,
            'koli' => (float) $m->koli,
            'brutto' => (float) $m->brutto,
            'mawb' => $m->mawb,
            'hawb' => $m->hawb,
            // 'uraian' => $m->uraian,
            'nama_importir' => $m->nama_importir,
            'alamat_importir' => $m->alamat_importir,

            'short_last_status' => $m->short_last_status,

            'ringkasan_uraian_barang' => $m->ringkasan_uraian_barang,

            'is_locked' => $m->is_locked,
            'waktu_gate_in' => (string) $m->waktu_gate_in
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

    public function includeLastTracking(EntryManifest $m) {
        $t = $m->last_tracking;
        if ($t) {
            return $this->item($t, new TrackingTransformer);
        }
    }

    public function includeLampiran(EntryManifest $m) {
        $l = $m->lampiran;
        return $this->collection($l, new LampiranTransformer);
    }
}
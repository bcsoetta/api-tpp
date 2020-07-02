<?php
namespace App\Transformers;

use App\EntryManifest;
use League\Fractal\TransformerAbstract;

class EntryManifestTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'tps',
        'bcp'
    ];

    protected $defaultIncludes = [
        'tps',
        'bcp'
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
            'uraian' => $m->uraian,
            'nama_importir' => $m->nama_importir,
            'alamat_importir' => $m->alamat_importir
        ];
    }

    public function includeTps(EntryManifest $m) {
        $t = $m->tps;

        return $this->item($t, new TPSTransformer);
    }

    public function includeBcp(EntryManifest $m) {
        $b = $m->bcp;

        if ($b) {
            return $this->item($b, new BCPTransformer);
        }
    }
}
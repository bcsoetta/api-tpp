<?php
namespace App\Transformers;

use App\PNBP;
use League\Fractal\TransformerAbstract;

class PNBPTransformer extends TransformerAbstract {
    
    protected $availableIncludes = [
        'pejabat',
        'entry_manifest'
    ];

    protected $defaultIncludes = [
        'pejabat'
    ];

    public function transform(PNBP $p) {
        return [
            'id' => (int) $p->id,
            'nomor_lengkap_dok' => (string) $p->nomor_lengkap_dok,
            'tgl_dok' => (string) $p->tgl_dok,
            'entry_manifest_id' => (int) $p->entry_manifest_id,
            'tgl_gate_in' => (string) $p->tgl_gate_in,
            'tgl_gate_out' => (string) $p->tgl_gate_out,
            'total_hari' => (int) $p->total_hari,
            'tarif_pnbp' => (float) $p->tarif_pnbp,
            'nilai_sewa' => (float) $p->nilai_sewa,
            'nama_bidang' => (string) $p->nama_bidang,
            'nama_jabatan' => (string) $p->nama_jabatan,
            'kode_surat' => (string) $p->kode_surat,
            'created_at' => (string) $p->created_at,
            'updated_at' => (string) $p->updated_at,

            'is_locked' => $p->is_locked,

            'tgl_gate_in_text' => formatTanggal($p->tgl_gate_in),
            'tgl_gate_out_text' => formatTanggal($p->tgl_gate_out),

            'brutto' => (float) $p->entryManifest->brutto,
            'pejabat_id' => (int) $p->pejabat_id
        ];
    }

    public function includePejabat(PNBP $p) {
        $pejabat = $p->pejabat;
        if ($pejabat) {
            return $this->item($pejabat, new SSOUserCacheTransformer);
        }
    }

    public function includeEntryManifest(PNBP $p) {
        $m = $p->entryManifest;
        if ($m) {
            return $this->item($m, new EntryManifestTransformer);
        }
    }
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PNBP extends AbstractDokumen
{
    // settings
    protected $table = 'pnbp';
    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'manual' => false
    ];

    // relation
    public function entryManifest() {
        return $this->belongsTo(EntryManifest::class, 'entry_manifest_id');
    }

    public function pejabat() {
        return $this->belongsTo(SSOUserCache::class, 'pejabat_id', 'user_id');
    }

    // computed attribs
    public function getJenisDokumenAttribute()
    {
        return 'pnbp';
    }

    public function getJenisDokumenLengkapAttribute()
    {
        return 'Penerimaan Negara Bukan Pajak';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'PNBP-' . $this->kode_surat;
    }

    public function getNomorLengkapAttribute()
    {
        if ($this->no_dok == 0) {
            return null;
        }

        if (strlen($this->nomor_lengkap_dok) > 0) {
            return $this->nomor_lengkap_dok;
        }
        
        $nomorLengkap = 'PNBP-'
                        .str_pad($this->no_dok, 5,"0", STR_PAD_LEFT)
                        .'/'
                        .$this->kode_surat
                        .'/'
                        .$this->tahun_dok;
        return $nomorLengkap;
    }

    /**
     * Generate PNBP from entry manifest
     */
    public static function generatePNBP(EntryManifest $m, $tglGateOut = null) {
        if (!$m) {
            throw new \Exception("Entry Manifest is null!");
        }

        // Entry Manifest harus punya penyelesaian
        if (!$m->penyelesaian()->count()) {
            throw new \Exception("Entry Manifest belum diselesaikan (dengan SPPB,REEKSPOR,BC16,dll)");
        }

        // pernah gate in ga?
        if (!$m->status()->byStatus('GATE-IN')->count()) {
            throw new \Exception("Entry Manifest belum pernah ditimbun! tidak bisa dibuatkan PNBP!");
        }

        // harus sudah berumur
        $m->waktu_gate_out_mockup = $tglGateOut;

        if (!$m->days_till_now) {
            throw new \Exception("Masa timbun di TPP < 1 hari, tidak bisa dibuatkan PNBP!");
        }

        // mungkin sudah gate out
        $gateout = Lokasi::byKode('GATEOUT')->first();
        if ($m->last_tracking->lokasi == $gateout) {
            throw new \Exception("Entry Manifest sudah digate out");
        }

        // okay, grab all necessary data
        $tgl_dok = $tglGateOut ?? date('Y-m-d');
        $tgl_gate_in = $m->waktu_gate_in->toDateString();
        $tgl_gate_out = $tgl_dok;
        $total_hari = min(60, $m->days_till_now);
        $tarif_pnbp = Setting::getValue('tarif_pnbp');
        
        // check tarif
        if (!$tarif_pnbp) {
            throw new \Exception("Tarif PNBP tidak ditemukan. Cek setting aplikasi!");
        }

        // hitung nilai_sewa
        $nilai_sewa = $tarif_pnbp * $total_hari * (float) $m->brutto;

        $pnbp = new PNBP([
            'tgl_dok' => $tgl_dok,
            'entry_manifest_id' => $m->id,
            'tgl_gate_in' => $tgl_gate_in,
            'tgl_gate_out' => $tgl_gate_out,
            'total_hari' => $total_hari,
            'tarif_pnbp' => $tarif_pnbp,
            'nilai_sewa' => $nilai_sewa
        ]);

        return $pnbp;
    }

    // recalculate PNBP (only valid if it's unlocked)
    public function recalculate(bool $force = false) {
        // first, gotta check if there's new tariff?
        $tarif_pnbp = Setting::getValue('tarif_pnbp');

        if (!$tarif_pnbp) {
            throw new \Exception("Tarif PNBP tidak ditemukan");
            return false;
        }

        // if locked already, fail by default unless we're admin
        if ($this->is_locked && !$force) {
            throw new \Exception("PNBP sudah terkunci!");
            return false;
        }

        // if there's no manifest, error
        if (!$this->entryManifest) {
            throw new \Exception("Tidak ditemukan entry manifest atas PNBP ini!");
            return false;
        }

        $total_hari = min(60, $this->entryManifest->days_till_now);

        // if days till now is zero, bail
        if (!$total_hari) {
            throw new \Exception("Masa timbun tidak mencukupi! baru {$total_hari} hari");
            return false;
        }

        // ok, we're safe
        $this->tarif_pnbp = $tarif_pnbp;
        $this->total_hari = $total_hari;
        $this->nilai_sewa = $tarif_pnbp * $total_hari * (float) $this->entryManifest->brutto;
        $this->save();

        return true;
    }
}

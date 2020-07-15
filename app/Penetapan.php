<?php

namespace App;


class Penetapan extends AbstractDokumen
{
    // settings
    protected $table = 'penetapan';
    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // relations
    public function pejabat() {
        return $this->belongsTo(SSOUserCache::class, 'pejabat_id', 'user_id');
    }

    public function entryManifest() {
        return $this->belongsToMany(EntryManifest::class, 'penetapan_detail', 'penetapan_id', 'entry_manifest_id')->withTimestamps();
    }

    // attribs
    public function getJenisDokumenAttribute()
    {
        return 'penetapan';
    }

    public function getJenisDokumenLengkapAttribute()
    {
        return 'Surat Penetapan';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'KPU.03/BD.0301';
    }

    public function getNomorLengkapAttribute()
    {
        if (strlen($this->nomor_lengkap_dok) > 0) {
            return $this->nomor_lengkap_dok;
        }
        
        if ($this->no_dok == 0) {
            return null;
        }

        $nomorLengkap = 'S-' 
            . str_pad( $this->no_dok, 6, '0', STR_PAD_LEFT ) 
            . '/' 
            . $this->skema_penomoran
            . '/'
            . $this->tahun_dok;
        return $nomorLengkap;
    }

    public function getGateInCountAttribute() {
        return $this->entryManifest()->byLastTracking(Lokasi::find(2))->count();
    }
    
    public function getNotGatedInCountAttribute() {
        return $this->entryManifest()->byLastTrackingOtherThan(Lokasi::find(2))->count();
    }

    public function getGateInPercentageAttribute() {
        // check total entry
        $totalEntryManifest = $this->entryManifest()->count();
        if (!$totalEntryManifest) {
            return 0;
        }

        // compute how many has gone into gate / total
        $totalGateIn = $this->gate_in_count;
        return (float) $totalGateIn / (float) $totalEntryManifest;
    }
}

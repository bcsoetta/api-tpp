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
}

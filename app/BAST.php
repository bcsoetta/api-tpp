<?php

namespace App;


class BAST extends AbstractDokumen
{
    // settings
    protected $table = 'bast';
    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // relations
    public function pejabat() {
        return $this->belongsTo(SSOUserCache::class, 'petugas_id', 'user_id');
    }

    public function entryManifest() {
        return $this->belongsToMany(EntryManifest::class, 'bast_detail', 'bast_id', 'entry_manifest_id')->withTimestamps();
    }

    // attributes
    public function getJenisDokumenAttribute()
    {
        return 'bast';
    }

    public function getJenisDokumenLengkapAttribute()
    {
        return 'Berita Acara Serah Terima';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'BAST/TPP/KPU.03';
    }

    public function getNomorLengkapAttribute()
    {
        if (strlen($this->nomor_lengkap_dok) > 0) {
            return $this->nomor_lengkap_dok;
        }

        if ($this->no_dok == 0) {
            return null;
        }

        $nomorLengkap = 'BAST-'
            . str_pad($this->no_dok, 6, '0', STR_PAD_LEFT)
            . '/KPU.03/BD.0301'
            . '/'
            . $this->tahun_dok;
        return $nomorLengkap;
    }
}

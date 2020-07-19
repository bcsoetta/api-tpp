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
    public function petugas() {
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
        return 'BA/TPP/KPU.03';
    }

    public function getNomorLengkapAttribute()
    {
        if (strlen($this->nomor_lengkap_dok) > 0) {
            return $this->nomor_lengkap_dok;
        }

        if ($this->no_dok == 0) {
            return null;
        }

        $nomorLengkap = 'BA-'
            . str_pad($this->no_dok, 6, '0', STR_PAD_LEFT)
            . '/TPP/KPU.03'
            . '/'
            . $this->tahun_dok;
        return $nomorLengkap;
    }
}

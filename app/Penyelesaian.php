<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Penyelesaian extends AbstractDokumen
{

    // table settings
    protected $table='penyelesaian';
    protected $guarded=[
        'created_at',
        'updated_at'
    ];

    // relation
    public function entryManifest() {
        return $this->belongsToMany(EntryManifest::class, 'penyelesaian_detail', 'penyelesaian_id', 'entry_manifest_id')->withTimestamps();
    }

    public function referensiJenisDokumen() {
        return $this->belongsTo(ReferensiDokumenPenyelesaian::class, 'jenis_dokumen_id');
    }

    public function petugas() {
        return $this->belongsTo(SSOUserCache::class, 'petugas_id', 'user_id');
    }

    // computed attribute
    public function getJenisDokumenAttribute()
    {
        return 'penyelesaian';
    }

    public function getJenisDokumenLengkapAttribute()
    {
        return 'Dokumen Penyelesaian utk Barang di TPP';
    }

    public function getSkemaPenomoranAttribute()
    {
        return null;
    }
}

<?php

namespace App;

class BACacah extends AbstractDokumen
{
    // settings
    protected $table = 'ba_cacah';

    // guarded
    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // relations
    public function pejabat() {
        return $this->belongsTo(SSOUserCache::class, 'pejabat_id', 'user_id');
    }

    public function penetapan() {
        return $this->belongsToMany(Penetapan::class, 'ba_cacah_detail_penetapan', 'ba_cacah_id', 'penetapan_id')->withTimestamps();
    }

    public function bast() {
        return $this->belongsToMany(BAST::class, 'ba_cacah_detail_bast', 'ba_cacah_id', 'bast_id')->withTimestamps();
    }

    public function pelaksana() {
        return $this->belongsToMany(SSOUserCache::class, 'ba_cacah_detail_pelaksana', 'ba_cacah_id', 'pelaksana_id', null, 'user_id')->withTimestamps();
    }

    public function entryManifest() {
        return $this->belongsToMany(EntryManifest::class, 'ba_cacah_detail', 'ba_cacah_id', 'entry_manifest_id')->withTimestamps();
    }

    // attributes
    public function getJenisDokumenAttribute()
    {
        return 'ba_cacah';
    }

    public function getJenisDokumenLengkapAttribute()
    {
        return 'Berita Acara Pencacahan';
    }

    public function getSkemaPenomoranAttribute()
    {
        return 'BA/TPP/KPU.03';
    }
}

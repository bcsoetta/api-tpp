<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BCP extends AbstractDokumen
{
    // settings
    protected $table = 'bcp';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'jenis' => 'BTD'
    ];

    // relations
    public function entryManifest() {
        return $this->belongsTo(EntryManifest::class, 'entry_manifest_id');
    }

    // scopes
    public function scopeJenis($query, $jenis) {
        return $query->where('jenis', $jenis);
    }

    // attributes
    public function getJenisDokumenAttribute()
    {
        return 'BCP';
    }

    public function getJenisDokumenLengkapAttribute()
    {
        return 'Barang Cacahan Pabean';
    }

    public function getSkemaPenomoranAttribute()
    {
        return $this->jenis . '-' . date('Y');
    }

    public function getNomorLengkapAttribute()
    {
        if ($this->no_dok == 0) {
            return null;
        }

        if (strlen($this->nomor_lengkap_dok) > 0) {
            return $this->nomor_lengkap_dok;
        }

        $nomorLengkap = $this->skema_penomoran . '/'. str_pad( $this->no_dok, 5, '0', STR_PAD_LEFT );
        return $nomorLengkap;
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pencacahan extends Model implements IHasGoods, ILockable
{
    use TraitHasGoods;
    use TraitLockable;
    use TraitLoggable;
    // table settings
    protected $table = 'pencacahan';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'kondisi_barang' => 'Baik',  // by default, assume baik
        'peruntukan_awal' => 'DILELANG'
    ];

    // relations
    public function petugas() {
        return $this->belongsTo(SSOUserCache::class, 'petugas_id', 'user_id');
    }

    public function entryManifest() {
        return $this->belongsTo(EntryManifest::class, 'entry_manifest_id');
    }
}

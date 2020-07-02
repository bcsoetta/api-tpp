<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pencacahan extends Model implements IHasGoods
{
    use TraitHasGoods;
    // table settings
    protected $table = 'pencacahan';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // relations
    public function petugas() {
        return $this->belongsTo(SSOUserCache::class, 'petugas_id', 'user_id');
    }

    public function entryManifest() {
        return $this->belongsTo(EntryManifest::class, 'entry_manifest_id');
    }
}

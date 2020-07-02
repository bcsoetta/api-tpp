<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TPS extends Model
{
    // setting
    protected $table = 'tps';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // relations
    public function entryManifest() {
        return $this->hasMany(EntryManifest::class, 'tps_id');
    }

    // scopes
    public function scopeByKode($query, $kode) {
        return $query->where('kode', $kode);
    }
}

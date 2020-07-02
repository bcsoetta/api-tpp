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

    // scopes
    public function scopeByKode($query, $kode) {
        return $query->where('kode', $kode);
    }
}

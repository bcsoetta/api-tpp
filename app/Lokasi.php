<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    // settings
    protected $table = 'lokasi';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'kode' => '',
        'nama' => ''
    ];

    // scope
    public function scopeByKode($query, $kode) {
        return $query->where('kode', $kode);
    }
}

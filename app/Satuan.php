<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    // settings
    protected $table = 'referensi_satuan';
    public $timestamps = false;

    public function scopeByKode($query, $kode) {
        return $query->where('kode', 'like', "%$kode%");
    }

    public function scopeByUraian($query, $nama) {
        return $query->where('uraian', 'like', "%$nama%");
    }

    public function scopeByExactKode($query, $kode) {
        return $query->where('kode', $kode);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferensiDokumenPenyelesaian extends Model
{
    // settings
    protected $table = 'referensi_dokumen_penyelesaian';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // scopes
    public function scopeName($query, $name) {
        return $query->where('nama', $name);
    }
}

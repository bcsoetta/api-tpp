<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailBarang extends Model
{
    // settings
    protected $table = 'detail_barang';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'uraian' => '',
        'jumlah' => null,
        'jenis' => null
    ];

    // relations 
    public function header() {
        return $this->morphTo();
    }
}

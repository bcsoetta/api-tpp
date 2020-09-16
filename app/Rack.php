<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rack extends Model
{
    // table settings
    protected $table = 'rack';
    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // let's do something

    // scope
    public function scopeByKode($query, $kode) {
        return $query->where('kode', 'like', "$kode%");
    }
}

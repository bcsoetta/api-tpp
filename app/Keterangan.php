<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Keterangan extends Model
{
    // settings
    protected $table = 'keterangan';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'keterangan' => ''
    ];

    // relations
    public function notable() {
        return $this->morphTo();
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    // settings
    protected $table = 'tracking';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // relations
    public function trackable() {
        return $this->morphTo();
    }

    public function lokasi() {
        return $this->morphTo();
    }

    public function petugas() {
        return $this->belongsTo(SSOUserCache::class, 'petugas_id', 'user_id');
    }
}

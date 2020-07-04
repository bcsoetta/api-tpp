<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lock extends Model
{
    // settings
    protected $table = 'lock';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // relation
    public function lockable() {
        return $this->morphTo();
    }

    public function petugas() {
        return $this->belongsTo(SSOUserCache::class, 'petugas_id', 'user_id');
    }
}

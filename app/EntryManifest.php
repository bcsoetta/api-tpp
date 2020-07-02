<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntryManifest extends Model
{
    // settings
    protected $table = 'entry_manifest';

    // relations
    public function tps() {
        return $this->belongsTo(TPS::class, 'tps_id');
    }
}

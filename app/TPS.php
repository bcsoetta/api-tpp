<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TPS extends Model
{
    // setting
    protected $table = 'tps';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // relations
    public function entryManifest() {
        return $this->hasMany(EntryManifest::class, 'tps_id');
    }

    // scopes
    public function scopeByKode($query, $kode) {
        return $query->where('kode', $kode);
    }

    // static helper
    public static function siapPenetapan() {
        return TPS::whereHas('entryManifest', function ($q) { $q->siapPenetapan(); })
            ->join('entry_manifest', function ($join) {
                $join->on('entry_manifest.tps_id', '=', 'tps.id');
            })
            ->whereNull('entry_manifest.deleted_at')
            ->select('tps.*', DB::raw('count(*) as total'))
            ->groupBy('tps.id');
    }
}

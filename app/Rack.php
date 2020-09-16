<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Rack extends Model
{
    // table settings
    protected $table = 'rack';
    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    // let's do something
    public function entryManifest() {
        $entry_manifest_ids = EntryManifest::byLastTracking($this)->get('id');

        return $this->hasManyThrough(EntryManifest::class,Tracking::class, 'lokasi_id', 'id', null, 'trackable_id')
        ->where('trackable_type', EntryManifest::class)
        ->where('lokasi_type', Rack::class)
        ->whereIn('trackable_id', $entry_manifest_ids->toArray())
        ;
    }

    // scope
    public function scopeByKode($query, $kode) {
        return $query->where('kode', 'like', "$kode%");
    }

    public function scopeIncludeTotal($query, $opcode = null, $optotal = null) {
        $q = Tracking::perTrackable()
            ->byLokasiType(Rack::class)
            ->byTrackableType(EntryManifest::class)
            ->groupBy('lokasi_id')
            ->select(['lokasi_id', DB::raw('COUNT(*) total')]);

        $subq1 = "( " . stringifyQuery($q) . ") t2";

        return $query
            ->select(['rack.*', DB::raw('IFNULL(t2.total, 0) total_awb') ])
            ->leftJoin(DB::raw($subq1), function ($join) {
                $join->on('rack.id', 't2.lokasi_id');
            })
            ->when(!is_null($opcode) && !is_null($optotal), function ($q1) use ($opcode, $optotal) {
                $q1->where('t2.total', $opcode, $optotal)
                    ->whereNotNull('t2.total');
            });
    }
}

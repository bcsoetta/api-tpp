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

        // grab all entry manifest ready for penetapan (summarized)
        $q2 = EntryManifest::siapPenetapan()
            ->groupBy('tps_id')
            ->select(DB::raw('COUNT(*) total'), 'tps_id');

        $q2string = "(" . stringifyQuery($q2) . ") t2";
        
        $q1 = TPS::query()
            ->select('tps.*', 't2.total')
            ->join(DB::raw($q2string), function($join) {
                $join->on('tps.id', '=', 't2.tps_id');
            });

        return $q1;
    }

    public static function siapRekamBAST() {
        // grab all entry manifest ready for penetapan (summarized)
        $q2 = EntryManifest::siapRekamBAST()
            ->groupBy('tps_id')
            ->select(DB::raw('COUNT(*) total'), 'tps_id');

        $q2string = "(" . stringifyQuery($q2) . ") t2";
        
        $q1 = TPS::query()
            ->select('tps.*', 't2.total')
            ->join(DB::raw($q2string), function($join) {
                $join->on('tps.id', '=', 't2.tps_id');
            });

        return $q1;
    }
}

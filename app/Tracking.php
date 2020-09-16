<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    // scopes
    public function scopeLastTrackingIds($query) {
        // list all last tracking info
        $ids = $query->select(DB::raw('MAX(tracking.id) id'));

        return $ids;
    }

    public function scopeLastTracking($query) {
        return $query
                ->join(
                    DB::raw("(" . 
                    stringifyQuery($query->lastTrackingIds())
                    ." GROUP BY trackable_id,trackable_type) last"),
                    function ($join) {
                        $join->on('tracking.id', 'last.id');
                    }
                )
                ->select(['*']);
    }

    public function scopeByLokasi($query, $lokasi) {
        return $query->where([
            'lokasi_type' => get_class($lokasi),
            'lokasi_id' => $lokasi->id
        ]);
    }

    public function scopeByLokasiOtherThan($query, $lokasi) {
        return $query->where('lokasi_type', '<>', get_class($lokasi))
                    ->orWhere('lokasi_id', '<>', $lokasi->id);
    }

    public function scopePerTrackable($query) {
        // grab latest per trackable
        return $query
                ->join(
                    DB::raw("
                    (SELECT
                        trackable_id tid,
                        trackable_type ttype,
                        MAX(tracking.id) maxid
                    FROM
                        tracking
                    GROUP BY
                        trackable_type,
                        trackable_id
                    ) stat
                    "),
                    function ($join) {
                        $join->on('tracking.trackable_id', '=', 'stat.tid');
                        $join->on('tracking.trackable_type', '=', 'stat.ttype');
                        $join->on('tracking.id', '=', 'stat.maxid');
                    }
                );
    }

    public function scopeLatestPerTrackable($query) {
        // grab latest per trackable
        return $query->latest()
                ->join(
                    DB::raw("
                    (SELECT
                        trackable_id tid,
                        trackable_type ttype,
                        MAX(tracking.id) maxid
                    FROM
                        tracking
                    GROUP BY
                        trackable_type,
                        trackable_id
                    ) stat
                    "),
                    function ($join) {
                        $join->on('tracking.trackable_id', '=', 'stat.tid');
                        $join->on('tracking.trackable_type', '=', 'stat.ttype');
                        $join->on('tracking.id', '=', 'stat.maxid');
                    }
                );
    }

    public function scopeByTrackableType($query, $trackable_type) {
        return $query->where('trackable_type', $trackable_type);
    }

    public function scopeByLokasiType($query, $lokasi_type) {
        return $query->where('lokasi_type', $lokasi_type);
    }
}

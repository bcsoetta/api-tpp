<?php
namespace App;

trait TraitTrackable {
    public function tracking() {
        return $this->morphMany(Tracking::class, 'trackable');
    }

    public function getLastTrackingAttribute() {
        return $this->tracking()->latest()->orderBy('id','desc')->first();
    }

    // scopes
    public function scopeByLastTracking($query, $lokasi) {
        $tIds = Tracking::latestPerTrackable()
                ->byTrackableType(get_class())
                ->byLokasi($lokasi)
                ->select(['tracking.trackable_id'])
                ->get();

        // build query
        return $query->whereIn("{$this->table}.id", $tIds);
    }

    public function scopeByLastTrackingOtherThan($query, $lokasi) {
        $tIds = Tracking::latestPerTrackable()
                ->byTrackableType(get_class())
                ->byLokasiOtherThan($lokasi)
                ->select(['tracking.trackable_id'])
                ->get();

        // build query
        return $query->whereIn("{$this->table}.id", $tIds);
    }
}
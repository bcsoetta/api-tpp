<?php
namespace App;

trait TraitTrackable {
    public function tracking() {
        return $this->morphMany(Tracking::class, 'trackable');
    }
}
<?php

namespace App;

trait TraitLockable
{
    public function lock() {
        return $this->morphOne(Lock::class, 'lockable');
    }

    public function getIsLockedAttribute() {
        // just test if our lock is there
        return $this->lock !== null;
    }

    public function unlock() {
        // just remove our lock
        $this->lock()->delete();
    }

    // query where lock
    public function scopeLocked($query) {
        return $query->whereHas('lock');
    }

    public function scopeUnlocked($query) {
        return $query->whereDoesnthave('lock');
    }
}

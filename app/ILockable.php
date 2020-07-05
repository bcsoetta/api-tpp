<?php

namespace App;

interface ILockable 
{
    // attain lock
    public function lock(); // return the lock
    public function unlock(); // delete the lock
    public function getIsLockedAttribute();

    // query where lock
    public function scopeLocked($query);
    public function scopeUnlocked($query);
}

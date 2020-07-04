<?php

namespace App;

interface ILockable 
{
    // attain lock
    public function lock(); // return the lock
    public function unlock(); // delete the lock

    // query where lock
    public function scopeLocked($query);
    public function scopeUnlocked($query);
}

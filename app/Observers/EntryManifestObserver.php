<?php

namespace App\Observers;

use App\EntryManifest;

class EntryManifestObserver
{
    // when created
    public function created(EntryManifest $m) {
        $m->appendStatus('CREATED');
    }
}

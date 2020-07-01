<?php
namespace App;

trait TraitAttachable {
    // hold all lampiran
    public function lampiran() {
        return $this->morphMany('App\Lampiran', 'attachable');
    }

    // instantiate all lampiran on disk
    public function instantiateAllLampiran() {
        $lampiran = $this->lampiran;

        $cnt = $lampiran->count();
        foreach ($lampiran as $l) {
            if (!$l->instantiateOnDisk()) $cnt--;
        }

        return $cnt;
    }
}
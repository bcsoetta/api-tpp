<?php
namespace App;

trait TraitStatusable {
    public function status() {
        return $this->morphMany('App\Status', 'statusable');
    }
    
    public function appendStatus($name, $lokasi = null, $keterangan = null, $linkable = null, $other_data = null) {
        // Better to create the status instance first
        $s = new Status(['status' => $name, 'lokasi' => $lokasi]);
        
        $this->status()->save(
            // Status::create(['status' => $name, 'lokasi' => $lokasi])
            $s
        );

        // attach and append status
        if ($keterangan || $linkable || $other_data) {
            $d = new StatusDetail([
                'keterangan'    => $keterangan,
                'other_data'    => $other_data
            ]);
            $d->linkable()->associate($linkable);

            $s->detail()->save($d);
        }

        return $s->refresh();
    }
}
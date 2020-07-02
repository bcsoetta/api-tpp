<?php
namespace App;

trait TraitStatusable {
    public function status() {
        return $this->morphMany('App\Status', 'statusable');
    }

    public function statusOrdered() {
        return $this->status()->latest()->orderBy('id', 'desc')->get();
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

    public function scopeByLastStatus($query, $status) {
        // first fetch all BPJ's last status
        $dokIds = Status::latestPerDoctype()
                        ->byDocType(get_class())
                        ->byStatus($status)
                        ->select(['status.statusable_id'])
                        ->get();
        // now find all bpj's whose id is in that
        return $query->whereIn('id', $dokIds);
    }

    public function scopeByLastStatusOtherThan($query, $status) {
        // first fetch all BPJ's last status
        $dokIds = Status::latestPerDoctype()
                        ->byDocType(get_class())
                        ->byStatusOtherThan($status)
                        ->select(['status.statusable_id'])
                        ->get();
        // now find all bpj's whose id is in that
        return $query->whereIn('id', $dokIds);
    }
}
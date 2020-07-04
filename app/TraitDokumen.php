<?php
namespace App;

/**
 * 
 */
trait TraitDokumen
{
    // all dokumen can have lampiran so use its trait
    use TraitAttachable;
    
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

    public function setNomorDokumen($force = false){
        if (!$force) {
            if ($this->no_dok != 0) {
                return ;
            }
        }

        // set no_dok
        $this->no_dok = getSequence($this->skema_penomoran, $this->tahun_dok);
        // set nomor_lengkap_dok using nomor_lengkap attribute
        $this->nomor_lengkap_dok = $this->nomor_lengkap;

        // $doctype = get_class($this);
        // echo "Setting doc number of {$doctype} using {$this->no_dok} and {$this->nomor_lengkap}\n";
        // save it
        $this->save();

        // if this got no status yet, create it
        $last_status = $this->last_status;

        if (!$last_status) {
            $this->appendStatus('CREATED');
        }
    }

    //=================================================================================================
    // COMPUTED PROPERTIES GO HERE!!
    //=================================================================================================
    // ambil data tahun dok dari tgl
    public function getTahunDokAttribute() {
        return (int)substr($this->tgl_dok, 0, 4);
    }

    // nomor lengkap, e.g. 000001/CD/T2F/SH/2019
    public function getNomorLengkapAttribute() {
        if ($this->no_dok == 0) {
            return null;
        }

        if (strlen($this->nomor_lengkap_dok) > 0) {
            return $this->nomor_lengkap_dok;
        }
        
        $nomorLengkap = str_pad($this->no_dok, 6,"0", STR_PAD_LEFT)
                        .'/'
                        .$this->skema_penomoran
                        .'/'
                        .$this->tahun_dok;
        return $nomorLengkap;
    }

    public function getLastStatusAttribute(){
        return $this->status()->latest()->orderBy('id', 'desc')->first();
    }

    public function getShortLastStatusAttribute() {
        $ls = $this->last_status;
        if ($ls) {
            return [
                'status'    => $ls->status,
                'created_at'=> (string) $ls->created_at
            ];
        }

        return null;
    }

    public function getUriAttribute() {
        return "/{$this->jenis_dokumen}/{$this->id}";
    }

    /**
     * RELATIONS (injected to all document)
     */

    public function status() {
        return $this->morphMany('App\Status', 'statusable');
    }

    public function statusOrdered() {
        return $this->status()->latest()->orderBy('id', 'desc')->get();
    }

    /**
     * SCOPES (injected to every relevant class)
     */
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

    public function scopeByNomorLengkap($query, $nomor_lengkap) {
        // just query teh nomor_lengkap_dok column
        return $query->where('nomor_lengkap_dok', 'like', "%{$nomor_lengkap}%");
    }
}

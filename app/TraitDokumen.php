<?php
namespace App;

/**
 * 
 */
trait TraitDokumen
{
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

    public function getUriAttribute() {
        return "/{$this->jenis_dokumen}/{$this->id}";
    }

    /**
     * RELATIONS (injected to all document)
     */

    
    /**
     * SCOPES (injected to every relevant class)
     */
    public function scopeByNomorLengkap($query, $nomor_lengkap) {
        // just query teh nomor_lengkap_dok column
        return $query->where('nomor_lengkap_dok', 'like', "%{$nomor_lengkap}%");
    }
}

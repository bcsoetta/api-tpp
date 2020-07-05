<?php

namespace App;

interface IDokumen
{
    public function getNomorLengkapAttribute();
    public function getJenisDokumenAttribute();
    public function getJenisDokumenLengkapAttribute();
    public function getSkemaPenomoranAttribute();
    
    /* public function getLastStatusAttribute();
    public function getShortLastStatusAttribute();
    public function status();
    public function appendStatus($name, $lokasi = null, $keterangan = null, $linkable = null, $other_data = null);
     */
    /* public function lock();
    public function unlock();
    public function getIsLockedAttribute(); */

    public function setNomorDokumen($force = false);
    public function getTahunDokAttribute();
}
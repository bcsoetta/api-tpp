<?php

namespace App;

interface IStatusable
{
    public function getLastStatusAttribute();
    public function getShortLastStatusAttribute();
    public function status();
    public function statusOrdered();
    public function appendStatus($name, $lokasi = null, $keterangan = null, $linkable = null, $other_data = null);
}

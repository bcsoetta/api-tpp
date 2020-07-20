<?php
namespace App;

trait TraitHasGoods {
    public function detailBarang() {
        return $this->morphMany(DetailBarang::class, 'header');
    }

    // attributes
    public function getRingkasanUraianBarangAttribute() {
        // just grab all detail barang and return the string
        $ret = '';

        $uraianPendek = $this->detailBarang->map(function($e) {
            return ($e->jumlah ? (float)$e->jumlah . ' ' : '')
                . ($e->jenis ? $e->jenis . ' ' : '')
                . $e->uraian;
        })->toArray();

        return implode(";\n", $uraianPendek);
    }
}
<?php
namespace App;

trait TraitHasGoods {
    public function detailBarang() {
        return $this->morphMany(DetailBarang::class, 'header');
    }

    // attributes
    public function getRingkasanUraianBarangAttribute() {
        // just grab all detail barang and return the string
        $uraianPendek = $this->detailBarang->map(function($e) {
            return ($e->jumlah ? (float)$e->jumlah . ' ' : '')
                . ($e->jenis ? $e->jenis . ' ' : '')
                . $e->uraian;
        })->toArray();

        return implode(";\n", $uraianPendek);
    }

    // scopes
    public function scopeByDetailBarang($query, $q='') {
        return $query->whereHas('detailBarang', function ($q1) use ($q) {
            $q1->where('uraian', 'like', "%$q%");
        });
    }
}
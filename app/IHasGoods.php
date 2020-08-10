<?php
namespace App;

interface IHasGoods {
    public function detailBarang();

    public function getRingkasanUraianBarangAttribute();

    // scope
    public function scopeByDetailBarang($query, $q='');
}
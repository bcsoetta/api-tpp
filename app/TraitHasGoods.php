<?php
namespace App;

trait TraitHasGoods {
    public function detailBarang() {
        return $this->morphMany(DetailBarang::class, 'header');
    }
}
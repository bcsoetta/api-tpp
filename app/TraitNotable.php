<?php
namespace App;

trait TraitNotable {
    public function keterangan() {
        return $this->morphMany(Keterangan::class, 'notable');
    }
}
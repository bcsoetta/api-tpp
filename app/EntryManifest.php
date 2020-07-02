<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntryManifest extends Model implements INotable, IHasGoods, ITrackable
{
    use TraitStatusable;
    use TraitNotable;
    use TraitHasGoods;
    use TraitTrackable;
    
    use SoftDeletes;
    // settings
    protected $table = 'entry_manifest';

    // relations
    public function tps() {
        return $this->belongsTo(TPS::class, 'tps_id');
    }

    public function bcp() {
        return $this->hasOne(BCP::class, 'entry_manifest_id');
    }

    // scopes
    public function scopeWild($query, $q) {
        return $query->awb($q)
                    ->orWhere(function ($query) use ($q) {
                        $query->importir($q);
                    })
                    ->orWhere(function ($query) use ($q) {
                        $query->byBCP($q);
                    });
    }

    public function scopeAwb($query, $q) {
        return $query->where('hawb', 'like', "%$q%")
                    ->orWhere('mawb', 'like', "%$q%");
    }

    public function scopeImportir($query, $namaImportir) {
        return $query->where('nama_importir', 'like', "%$namaImportir%");
    }

    public function scopeFrom($query, $tgl) {
        return $query->where('tgl_bc11', '>=', $tgl);
    }

    public function scopeTo($query, $tgl) {
        return $query->where('tgl_bc11', '<=', $tgl);
    }

    public function scopeByBCP($query, $nomor) {
        return $query->whereHas('bcp', function($query) use ($nomor) {
            $query->where('nomor_lengkap_dok', 'like', "%$nomor%")
                ->orWhere('no_dok', $nomor);
        });
    }

    public function scopeTps($query, $kode) {
        return $query->whereHas('tps', function ($query) use ($kode) {
            $query->byKode($kode);
        });
    }

    public function scopeBelumBCP($query) {
        return $query->whereDoesntHave('bcp');
    }

    public function scopeSudahBCP($query) {
        return $query->whereHas('bcp');
    }
}

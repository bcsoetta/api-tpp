<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntryManifest extends Model implements INotable, IHasGoods, ITrackable, ILockable
{
    use TraitStatusable;
    use TraitNotable;
    use TraitHasGoods;
    use TraitTrackable;
    use TraitLockable;
    use TraitLoggable;
    
    use SoftDeletes;
    // settings
    protected $table = 'entry_manifest';

    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // relations
    public function tps() {
        return $this->belongsTo(TPS::class, 'tps_id');
    }

    public function bcp() {
        return $this->hasOne(BCP::class, 'entry_manifest_id');
    }

    public function pencacahan() {
        return $this->hasOne(Pencacahan::class, 'entry_manifest_id');
    }

    public function penetapan() {
        return $this->belongsToMany(Penetapan::class, 'penetapan_detail', 'entry_manifest_id', 'penetapan_id')->withTimestamps();
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

    // siap penetapan := doesnthave Penetapan and unlocked
    public function scopeSiapPenetapan($query) {
        /* return $query->whereDoesnthave('bcp')
                    ->unlocked(); */
        return $query->whereDoesntHave('penetapan')
                    ->unlocked();
    }

    // siap gatein := have penetapan and location is not TPP yet
    public function scopeSiapGateIn($query) {
        return $query->whereHas('penetapan')
                    ->where(function ($q) {
                        $q->byLastTrackingOtherThan(Lokasi::find(2));
                    })
                    ->unlocked();
    }
}

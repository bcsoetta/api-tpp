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
    use TraitAttachable;
    
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

    public function bast() {
        return $this->belongsToMany(BAST::class, 'bast_detail', 'entry_manifest_id', 'bast_id')->withTimestamps();
    }

    public function baCacah() {
        return $this->belongsToMany(BACacah::class, 'ba_cacah_detail', 'entry_manifest_id', 'ba_cacah_id')->withTimestamps();
    }

    // custom attributes
    public function getWaktuGateInAttribute() {
        $t = $this->tracking()->byLokasi(Lokasi::find(2))->first();
        if ($t) {
            return $t->created_at;
        }
        return null;
    }

    public function getPosFormattedAttribute() {
        return str_pad($this->pos, 4, '0', STR_PAD_LEFT) . '.'
        .   str_pad($this->subpos, 4, '0', STR_PAD_LEFT) . '.'
        .   str_pad($this->subsubpos, 4, '0', STR_PAD_LEFT);
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

    public function scopeByDetailPencacahan($query, $q='') {
        return $query->whereHas('pencacahan', function ($q1) use ($q) {
            $q1->byDetailBarang($q);
        });
    }

    // siap penetapan := doesnthave Penetapan and unlocked
    public function scopeSiapPenetapan($query) {
        /* return $query->whereDoesnthave('bcp')
                    ->unlocked(); */
        return $query->whereDoesntHave('penetapan')
                    ->whereDoesntHave('bcp')
                    ->unlocked();
    }

    // siap gatein := have penetapan and not having bcp and location is not TPP yet
    public function scopeSiapGateIn($query) {
        // Logic is
        // where ( hasPenetapan  ) AND LAST LOKASI <> TPP AND EM is UNLOCKED
        return $query->whereHas('penetapan')
        ->where(function ($q) {
            $q->byLastTrackingOtherThan(Lokasi::find(2));
        })
        ->unlocked();
    }

    // siap rekam bast := lasttracking in tpp, and has no bast yet
    public function scopeSiapRekamBAST($query) {
        return $query->whereDoesntHave('bast')
                    ->where(function ($q) {
                        $q->byLastTracking(Lokasi::find(2));
                    })
                    ->unlocked();
    }

    // siap pencacahan := belum ada data cacah, sudah gate in?
    public function scopeSiapPencacahan($query) {
        return $query->whereDoesntHave('pencacahan')
                    ->where(function ($q1) {
                        $q1->byLastStatus('BAST');
                    })
                    ->unlocked();
    }

    // siap rekam ba cacah := sudah ada pencacahan tapi belum ada baCacah
    public function scopeSiapRekamBACacah($query) {
        // has pencacahan
        return $query->whereHas('pencacahan', function ($q1) {
                    // only if it has detail barang at least 1
                    $q1->whereHas('detailBarang');
                })
                // but doesn't have baCacah
                ->whereDoesntHave('baCacah')
                ->unlocked();
    }

    // barang ex bdn
    public function scopeDariKepBDN($query) {
        return $query->whereHas('bcp', function ($q) {
            $q->where('jenis', 'BDN');
        });
    }
}

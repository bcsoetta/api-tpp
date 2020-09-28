<?php

namespace App;

use Carbon\Carbon;
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

    public function penyelesaian() {
        return $this->belongsToMany(Penyelesaian::class, 'penyelesaian_detail', 'entry_manifest_id', 'penyelesaian_id')->withTimestamps();
    }

    public function pnbp() {
        return $this->hasOne(PNBP::class, 'entry_manifest_id');
    }

    // custom attributes
    public function getWaktuGateInAttribute() {
        $t = $this->tracking()->byLokasi(Lokasi::find(2))->first();
        if ($t) {
            return $t->created_at;
        }
        return null;
    }

    public function getWaktuGateOutAttribute() {
        $t = $this->tracking()->byLokasi(Lokasi::find(3))->first();
        if ($t) {
            return $t->created_at;
        }
        return null;
    }

    public function getDaysTillNowAttribute() {
        // kalau belum pernah digate in, berarti 0 (gratis)
        if (!$this->waktu_gate_in) {
            return 0;
        }

        $start = date_create($this->waktu_gate_in->toDateString());
        $end = date_create( date('Y-m-d') );
        $diff = date_diff($start, $end);
        return $diff->days;
    }

    public function getWaktuTimbunAttribute() {
        $start =  date_create($this->waktu_gate_in ? $this->waktu_gate_in->toDateString() : '') ;
        $end =  date_create($this->waktu_gate_out ? $this->waktu_gate_out->toDateString() : '') ;
        return date_diff($start, $end)->days;
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
                ;
                // ->unlocked();
    }

    // siap dipnbp (udh berumur sejak gate in + sudah ada penyelesaian)
    public function scopeSiapPNBP($query) {
        return $query->whereHas('penyelesaian')
                    ->whereDoesntHave('pnbp')
                    ->agedSinceGateIn()
                    ->hasGateOut(false)
                    ->locked();
    }

    // agedSinceGateIn
    public function scopeAgedSinceGateIn($query) {
        return $query->whereHas('status', function ($q) {
            $q->where('status', 'GATE-IN')
                ->whereRaw('DATEDIFF(NOW(), status.created_at) > 0');
        });
    }

    // hasAged
    public function scopeAgeSinceGateIn($query, $op='>=', $age=0) {
        return $query->whereHas('status', function ($q) use ($op, $age) {
            $q->where('status', 'GATE-IN')
                ->whereRaw("DATEDIFF(NOW(), status.created_at) $op $age");
        });
    }

    // siap digate out := sudah ada penyelesaian & belum gate out & PERNAH gate in
    public function scopeSiapGateOut($query) {
        return $query->whereHas('penyelesaian')
                    ->locked()
                    ->hasGateOut(false)
                    ->agedSinceGateIn()
                    ;
    }

    // sudah gate out
    public function scopeHasGateOut($query, $flag = true) {
        $lokasi_gate_out = Lokasi::byKode('GATEOUT')->first();

        if ($flag) {
            return $query->whereHas('tracking', function ($q) use ($lokasi_gate_out) {
                $q->byLokasi($lokasi_gate_out);
            });
        }

        // check for negatives
        return $query->whereDoesntHave('tracking', function ($q) use ($lokasi_gate_out) {
            $q->byLokasi($lokasi_gate_out);
        });
    }

    // barang ex bdn
    public function scopeDariKepBDN($query) {
        return $query->whereHas('bcp', function ($q) {
            $q->where('jenis', 'BDN');
        });
    }

    // scope siap bmn
    // BTD
    public function scopeBTDSiapBMN($query) {
        // either BTD >= 60 hr OR BDN >= 30 hr
        return $query->whereHas('bcp', function ($q) { $q->where('jenis', 'BTD'); })
                    ->ageSinceGateIn('>=', 60);
    }
    // BDN
    public function scopeBDNSiapBMN($query) {
        // either BTD >= 60 hr OR BDN >= 30 hr
        return $query->whereHas('bcp', function ($q) { $q->where('jenis', 'BDN'); })
                    ->ageSinceGateIn('>=', 30);
    }
    // ALL
    public function scopeSiapBMN($query) {
        return $query->BTDSiapBMN()
                    ->orWhere(function ($query) {
                        $query->BDNSiapBMN();
                    });
    }

    // rollback Gate In
    public function rollbackGateIn() {
        // grab em
        $m = $this;

        if ( !($m->last_status && $m->last_status->status == 'GATE-IN') ) {
            throw new \Exception("Cuma AWB yg status gate-in aja y bsa dirollback status GATE-IN nya");
        }

        // start from status
        $m->last_status->detail->delete();
        $m->last_status->delete();

        // delete bcp
        $m->bcp->delete();

        // delete tracking
        if (!($m->last_tracking && $m->last_tracking->lokasi->kode == 'TPPSH')) {
            throw new \Exception("Last tracking bukan di TPP!");
        }
        $m->last_tracking->delete();
    }
}

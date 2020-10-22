<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\EntryManifest;
use App\Lokasi;
use App\PNBP;
use App\SSOUserCache;
use App\Tracking;
use App\Transformers\PNBPTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PNBPController extends ApiController
{
    // gotta index it
    public function index(Request $r) {
        $from = $r->get('from');
        $to = $r->get('to');
        $q = $r->get('q');

        $query = PNBP::query()
                // use from date if available
                ->when($from, function ($q) use ($from) {
                    $q->where('tgl_dok', '>=', $from);
                })
                // use to date if available
                ->when($to, function ($q) use ($to) {
                    $q->where('tgl_dok', '<=', $to);
                })
                // use wildcard query if parameter 'q' is supplied
                ->when($q, function ($q1) use ($q) {
                    $q1->where('nomor_lengkap_dok', 'like', "%$q%")
                    ->orWhereHas('entryManifest', function ($q2) use ($q) {
                        $q2->wild($q)
                            ->orWhere(function ($q3) use ($q) {
                                $q3->awb($q);
                            });
                    })
                    ;
                })
                ->latest()
                ->orderBy('id', 'desc')
                ;
        $paginator = $query->paginate($r->get('number') ?? 10)
                            ->appends($r->except('page'));
        return $this->respondWithPagination($paginator, new PNBPTransformer);
    }

    // show a specific PNBP
    public function show(Request $r, $id) {
        try {
            // grab it
            $pnbp = PNBP::findOrFail($id);

            return $this->respondWithItem($pnbp, new PNBPTransformer);
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * patch type. Several operation supported
     * - recalculate/perbarui-pungutan: recompute non-tax levy
     * - tandai-lunas: mark as paid, append status to entry_manifest data
     */
    public function patch(Request $r, $id) {
        
        DB::beginTransaction();

        try {
            // decode json body
            $ops = json_decode($r->getContent());

            // read pnbp
            $pnbp = PNBP::findOrFail($id);

            // for each operation, do something
            foreach ($ops as $op) {
                switch ($op->op) {
                case 'recalculate':
                case 'perbarui-pungutan':
                    // try to recalculate
                    $pnbp->recalculate();
                break;

                case 'tandai-lunas':
                    // tandai lunas (kunci pnbpnya)
                    // gotta check for lampiran
                    if (!$pnbp->lampiran()->count()) {
                        throw new \Exception("Belum ada bukti bayar yang diupload!");
                    }

                    // safe to continue, grab manifest data
                    $m = $pnbp->entryManifest;
                    if (!$m) {
                        throw new \Exception("PNBP ini tidak ada dokumen dasarnya!");
                    }

                    // check apakah perlu diperbaharui perhitungannya
                    if ($pnbp->total_hari != $m->days_till_now) {
                        throw new \Exception("Total hari di TPP sudah berbeda dari perhitungan awal (awal: {$pnbp->total_hari} hari, sekarang: {$m->days_till_now} hari). Harap perbarui perhitungan PNBP!");
                    }

                    // first, lock the pnbp
                    $pnbp->lock()->create([
                        'petugas_id' => $r->userInfo['user_id']
                    ]);

                    // append status utk entry manifestnya
                    $m->appendStatus(
                        'PNBP LUNAS',
                        null, 
                        "Bukti Pelunasan PNBP sudah direkam oleh {$r->userInfo['username']}", 
                        $pnbp
                    );

                    AppLog::logInfo("Bukti lunas PNBP #{$pnbp->id} direkam oleh {$r->userInfo['username']}", $pnbp, false);
                break;
                
                default:
                    throw new \Exception("Invalid op: [{$op->op}]!");
                }
            }

            // commit and return 204
            DB::commit();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Precalculate PNBP, so when storing it, operator knows how much to charge, etc
     */
    public function precalculatePNBP(Request $r, $id) {
        try {
            // grab data
            $m = EntryManifest::findOrFail($id);

            // try to generate PNBP from entry manifest
            $pnbp = PNBP::generatePNBP($m, $r->get('tgl_gate_out'));
            $pnbp->manual = $r->get('manual') ?? false;

            return $this->respondWithItem($pnbp, new PNBPTransformer);
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Store PNBP, post. Make sure it's valid
     */
    public function store(Request $r) {
        DB::beginTransaction();
        try {
            // grab request data
            $entry_manifest_id = expectSomething($r->get('entry_manifest_id'), 'ID Entry Manifest');
            $pejabat_id = expectSomething($r->get('pejabat_id'), 'ID Pejabat ttd');
            $nama_bidang = expectSomething($r->get('nama_bidang'), 'Nama Bidang');
            $nama_jabatan = expectSomething($r->get('nama_jabatan'), 'Nama Jabatan Seksi');
            $kode_surat = expectSomething($r->get('kode_surat'), 'Kode Surat PNBP');
            $no_dok = expectSomething($r->get('no_dok'), 'Nomor Urut PNBP');
            $tgl_dok = expectSomething($r->get('tgl_dok'), 'Tanggal PNBP');
            $tgl_gate_out = $r->get('tgl_gate_out');
            $manual = $r->get('manual') ?? false;

            // pastikan no_dok adalah nomor
            if (!(is_numeric($no_dok) && is_integer($no_dok) && $no_dok > 0 )) {
                throw new \Exception("nomor urut PNBP tidak valid (bukan angka)!");
            }

            // gotta check if there exist a pnbp with that number
            if (PNBP::where('no_dok', $no_dok)->count() > 0) {
                throw new \Exception("Nomor urut PNBP '{$no_dok}' sudah terpakai!");
            }
            
            // grab entry manifest, and check
            $m = EntryManifest::findOrFail($entry_manifest_id);

            // check if it's already have pnbp
            $pnbp = $m->pnbp;
            if ($pnbp) {
                throw new \Exception("Entry Manifest sudah direkam PNBPnya!");
            }

            // create pnbp dari entry manifest
            $pnbp = PNBP::generatePNBP($m, $tgl_gate_out);

            // if success, append data
            // $pnbp = new PNBP();
            $pnbp->pejabat()->associate(SSOUserCache::byId($pejabat_id));
            $pnbp->nama_bidang = $nama_bidang;
            $pnbp->nama_jabatan = $nama_jabatan;
            $pnbp->kode_surat = $kode_surat;

            // set penomoran manual
            $pnbp->no_dok = $no_dok;
            $pnbp->tgl_dok = $tgl_dok;
            $pnbp->nomor_lengkap_dok = $pnbp->nomor_lengkap;
            $pnbp->manual = $manual;
            // save, and set nomor dokumen
            $pnbp->save();
            // $pnbp->setNomorDokumen();

            // log the entry manifest?
            $m->appendStatus(
                'PNBP CREATED', 
                null, 
                "PNBP telah direkam oleh {$r->userInfo['username']} dengan nomor {$pnbp->nomor_lengkap_dok} tanggal {$pnbp->tgl_dok}", 
                $pnbp
            );

            // log it
            AppLog::logInfo(
                "PNBP #{$pnbp->id} telah direkam oleh {$r->userInfo['username']}",
                $pnbp,
                false
            );


            // kalo manual, skalian rekam bukti lunas + gate out
            if ($manual) {
                // first, lock the pnbp
                $pnbp->lock()->create([
                    'petugas_id' => $r->userInfo['user_id']
                ]);

                // append status utk entry manifestnya
                $m->appendStatus(
                    'PNBP LUNAS',
                    null, 
                    "Bukti Pelunasan PNBP sudah direkam oleh {$r->userInfo['username']}", 
                    $pnbp
                );

                AppLog::logInfo("Bukti lunas PNBP #{$pnbp->id} direkam oleh {$r->userInfo['username']}", $pnbp, false);
                
                // pastikan belum gate out
                $hasGateOut = $m->tracking()->byLokasi(Lokasi::byKode('GATEOUT')->first())->count();

                // only process if hasn't gate out
                if (!$hasGateOut) {
                    // first, append status Gate Out
                    // $m = new EntryManifest();
                    $m->appendStatus(
                        'GATE-OUT',
                        null,
                        "Entry Manifest sudah di Gate-Out oleh {$r->userInfo['username']}"
                    );

                    // update tracking
                    $t = new Tracking();
                    $t->petugas()->associate(SSOUserCache::byId($r->userInfo['user_id']));
                    $t->lokasi()->associate(Lokasi::byKode('GATEOUT')->first());

                    $t->created_at = $t->updated_at = $tgl_gate_out ?? date('Y-m-d') . ' ' . date('H:i:s');

                    $m->tracking()->save($t, ['timestamps' => false]);

                    // log
                    AppLog::logInfo(
                        "Entry Manifest #{$m->id} di gate out oleh {$r->userInfo['username']}",
                        $m,
                        false
                    );
                }
            }

            // commit
            DB::commit();

            // return the newly created item
            return $this->respondWithItem($pnbp, new PNBPTransformer);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }
}

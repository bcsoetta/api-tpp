<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\EntryManifest;
use App\PNBP;
use App\SSOUserCache;
use App\Transformers\PNBPTransformer;
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

    // patch type
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
                    // try to recalculate
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
            $pnbp = PNBP::generatePNBP($m);

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
            
            // grab entry manifest, and check
            $m = EntryManifest::findOrFail($entry_manifest_id);

            // check if it's already have pnbp
            $pnbp = $m->pnbp;
            if ($pnbp) {
                throw new \Exception("Entry Manifest sudah direkam PNBPnya!");
            }

            // create pnbp dari entry manifest
            $pnbp = PNBP::generatePNBP($m);

            // if success, append data
            // $pnbp = new PNBP();
            $pnbp->pejabat()->associate(SSOUserCache::byId($pejabat_id));
            $pnbp->nama_bidang = $nama_bidang;
            $pnbp->nama_jabatan = $nama_jabatan;
            $pnbp->kode_surat = $kode_surat;

            // save, and set nomor dokumen
            $pnbp->save();
            $pnbp->setNomorDokumen();

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

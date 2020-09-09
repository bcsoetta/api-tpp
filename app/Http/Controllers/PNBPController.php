<?php

namespace App\Http\Controllers;

use App\EntryManifest;
use App\PNBP;
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
}

<?php

namespace App\Http\Controllers;

use App\EntryManifest;
use App\Transformers\EntryManifestTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class EntryManifestController extends ApiController
{
    // query
    public function index(Request $r) {
        $q = $r->get('q');
        $awb = $r->get('hawb') ?? $r->get('mawb');
        $consignee = $r->get('importir') ?? $r->get('consignee');
        $from = $r->get('from') ?? $r->get('tgl_awal');
        $to = $r->get('to') ?? $r->get('tgl_akhir');
        $bcp = $r->get('bcp');
        $tps = $r->get('tps') ?? $r->get('gudang');
        $tps_id = $r->get('tps_id');

        $has_bcp = $r->get('has_bcp');

        $status = $r->get('status');

        $query = EntryManifest::query()
            ->when($awb, function ($query) use ($awb) {
                $query->awb($awb);
            })
            ->when($q, function ($query) use ($q) {
                $query->wild($q);
            })
            ->when($consignee, function ($query) use ($consignee) {
                $query->importir($consignee);
            })
            ->when($from, function ($query) use ($from) {
                $query->from($from);
            })
            ->when($to, function ($query) use ($to) {
                $query->to($to);
            })
            ->when($bcp, function ($query) use ($bcp) {
                $query->byBCP($bcp);
            })
            ->when($tps, function ($query) use ($tps) {
                $query->tps($tps);
            })
            ->when($tps_id, function ($query) use ($tps_id) {
                $query->whereHas('tps', function ($query) use ($tps_id) {
                    $query->where('id', $tps_id);
                });
            })
            ->when($has_bcp, function ($query) use ($has_bcp) {
                if ($has_bcp == 'true') {
                    $query->sudahBCP();
                } else {
                    $query->belumBCP();
                }
            })
            ->when($status, function ($query) use ($status) {
                $query->byLastStatus($status);
            })
            ->latest()
            ->orderBy('id', 'desc')
        ;

        $paginator = $query->paginate($r->get('number', 10))
                            ->appends($r->except('page'));
        return $this->respondWithPagination($paginator, new EntryManifestTransformer);
    }

    // show by id
    public function show(Request $r, $id) {
        try {
            $m = EntryManifest::findOrFail($id);

            return $this->respondWithItem($m, new EntryManifestTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("Data AWB dengan id #$id tidak ditemukan");
        } catch (Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}

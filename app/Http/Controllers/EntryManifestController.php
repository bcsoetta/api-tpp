<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\DetailBarang;
use App\EntryManifest;
use App\Keterangan;
use App\Lokasi;
use App\SSOUserCache;
use App\TPS;
use App\Tracking;
use App\Transformers\EntryManifestTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDOException;
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

    /**
     * post data from parsed excel data, with following format:
     * {
     *  tps_id: 2,
     *  entry_manifest: [
     *   EntryManifest + Keterangan + Detail Barang
     *  ]
     * }
     * */ 
    public function postFromExcel(Request $r) {
        DB::beginTransaction();
        try {
            // grab TPS Instance
            $tps = TPS::byKode($r->get('tps_kode'))->first();

            $total = 0;

            // loop over all entry_manifest
            $ems = $r->get('entry_manifest', []);

            foreach ($ems as $em) {
                $barangs = $em['barang']['data'];
                $keterangans = $em['keterangan']['data'];

                // unset it 
                unset($em['keterangan']);
                unset($em['barang']);
                unset($em['status']);
                unset($em['tracking']);

                // insert entry_manifest
                $m = new EntryManifest($em);
                $m->tps()->associate($tps);
                $m->save();

                // insert keterangan
                foreach ($keterangans as $keterangan) {
                    $k = new Keterangan($keterangan);
                    $m->keterangan()->save($k);
                }

                // insert barang?
                foreach ($barangs as $brg) {
                    $b = new DetailBarang($brg);
                    $m->detailBarang()->save($b);
                }

                // update tracking location to tps 
                $t = new Tracking();
                $t->petugas()->associate(SSOUserCache::byId($r->userInfo['user_id']));
                $t->trackable()->associate($m);
                $t->lokasi()->associate($tps);
                $t->save();

                ++$total;
            }

            //code...
            DB::commit();

            return $this->respondWithArray([
                'inserted' => $total,
                'tps_id' => $tps->id
            ]);
        } catch (PDOException $e) {
            DB::rollBack();

            return $this->errorBadRequest("Duplikat HAWB/MAWB pada baris ke: " . ($total+1));
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Delete an EntryManifest
     */
    public function destroy(Request $r, $id) {
        try {
            // find it first
            $m = EntryManifest::findOrFail($id);

            AppLog::logInfo("HAWB #$id dihapus oleh ".$r->userInfo['username'], $m);

            $m->delete();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("HAWB #$id tidak ditemukan");
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * index siap penetapan
     */
    public function indexSiapGateIn(Request $r) {
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

        $query = EntryManifest::siapGateIn()
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

    /**
     * storeGateIn
     * mark AWB as gated in
     */
    public function storeGateIn(Request $r, $id) {
        DB::beginTransaction();

        try {
            // first, grab the awb
            $m = EntryManifest::findOrFail($id);

            // gotta check if it's already gated in?
            $lastLoc = $m->last_tracking->lokasi;
            $tpp = Lokasi::find(2);

            if (get_class($lastLoc) == get_class($tpp) && $lastLoc->id == $tpp->id) {
                // already in TPP!! BAIL!!
                throw new \Exception("AWB ini sudah ada di tpp!");
            }

            // safe to continue
            // first, update status
            $m->appendStatus('GATE-IN');

            // next, update tracking info
            $t = new Tracking();
            $t->lokasi()->associate($tpp);
            $t->petugas()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            $t->trackable()->associate($m);
            $t->save();

            // log it?
            AppLog::logInfo("AWB #{$id} telah di gate-in oleh {$r->userInfo['username']}", $m, false);

            DB::commit();

            // return empty
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("Entry Manifest #{$id} was not found");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }
}

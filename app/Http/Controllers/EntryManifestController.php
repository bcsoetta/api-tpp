<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\BCP;
use App\DetailBarang;
use App\EntryManifest;
use App\Keterangan;
use App\Lokasi;
use App\Penetapan;
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

        $siap_rekam_bast = $r->get('siap_rekam_bast');
        $dari_kep_bdn = $r->get('dari_kep_bdn');

        $siap_pencacahan = $r->get('siap_pencacahan');
        $has_pencacahan = $r->get('has_pencacahan');

        $siap_rekam_ba_cacah = $r->get('siap_rekam_ba_cacah');

        $orderBy = $r->get('orderBy');

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
            ->when($siap_rekam_bast, function ($query) {
                $query->siapRekamBAST();
            })
            ->when($dari_kep_bdn, function ($query) {
                $query->dariKepBDN();
            })
            ->when($siap_pencacahan, function ($query) {
                $query->siapPencacahan();
            })
            ->when($has_pencacahan, function ($query) use ($has_pencacahan) {
                if ($has_pencacahan == 'true')
                    $query->whereHas('pencacahan');
                else
                    $query->whereDoesntHave('pencacahan');
            })
            ->when($siap_rekam_ba_cacah, function ($query) {
                $query->siapRekamBACacah();
            })
            ->when($status, function ($query) use ($status) {
                $query->byLastStatus($status);
            })
            // when no definite order set, order by latest and then id in descending order
            // (newest shown first)
            ->when(!$orderBy, function ($query) {
                $query->latest()
                    ->orderBy('id', 'desc');
            })
            // when orderBy is set
            ->when($r->get('orderBy'), function ($query) use ($r) {
                $orderBy = $r->get('orderBy');
                $orderBy = explode(',',$orderBy);
                $query->select('entry_manifest.*');
                foreach ($orderBy as $ord) {
                    $ord = explode('|', $ord);
                    if ($ord[0] == 'bcp') {
                        $query->with('bcp')->leftJoin('bcp', 'bcp.entry_manifest_id', '=', 'entry_manifest.id')
                            ->orderBy('bcp.nomor_lengkap_dok', $ord[1])
                        ;
                    }
                }
            })
        ;

        $number = $r->get('show_all') ? $query->count() : $r->get('number', 10);

        $paginator = $query->paginate($number)
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
        // if source is from kep-bdn, call appropriate handler
        if ($r->get('source') == 'kep-bdn') {
            return $this->postFromExcelKepBdn($r);
        }

        // reach here means source is undefined. assume it's from tps
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


                // insert entry_manifest
                $m = new EntryManifest([
                    'koli' => $em['koli'],
                    'brutto' => $em['brutto'],
                    'mawb' => $em['mawb'],
                    'hawb' => $em['hawb'],
                    'nama_importir' => $em['nama_importir'],
                    'alamat_importir' => $em['alamat_importir'],

                    'no_bc11' => $em['no_bc11'],
                    'tgl_bc11' => $em['tgl_bc11'],
                    'pos' => $em['pos'],
                    'subpos' => $em['subpos'],
                    'subsubpos' => $em['subsubpos'],
                    'kd_flight' => $em['kd_flight'],
                ]);

                $m->tps()->associate($tps);
                $m->save();

                // insert keterangan
                foreach ($keterangans as $keterangan) {
                    if ($keterangan['keterangan']) {
                        $k = new Keterangan($keterangan);
                        $m->keterangan()->save($k);
                    }
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

            return $this->errorBadRequest("Data duplikat pada baris: " . ($total + 1) . "\nDetail: " . $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * post data from parsed excel data, with following format:
     * {
     *  entry_manifest: [
     *   EntryManifest + BCP + Keterangan + Detail Barang
     *  ]
     * }
     * */ 
    public function postFromExcelKepBdn(Request $r) {
        DB::beginTransaction();

        try {
            $total = 0; // how many inserted?
            $lokasi = Lokasi::find(1);  // gudang P2

            // grab header data?
            $nomor_lengkap_dok = expectSomething($r->get('nomor_lengkap_dok'), 'Nomor Kep Penetapan');
            $tgl_dok = expectSomething($r->get('tgl_dok'), 'Tanggal Kep Penetapan');
            $pejabat_id = expectSomething($r->get('pejabat_id'), 'Pejabat Penetapan');

            // store penetapan first
            $pejabat = SSOUserCache::byId($pejabat_id);

            $p = new Penetapan([
                'nomor_lengkap_dok' => $nomor_lengkap_dok,
                'tgl_dok' => $tgl_dok,
                'jenis' => 'KEP_BDN'
            ]);
            $p->pejabat()->associate($pejabat);
            $p->save();

            // loop over all entry manifest
            $ems = $r->get('entry_manifest', []);

            foreach ($ems as $em) {
                // grab columns data
                $barangs = $em['barang']['data'];
                $keterangans = $em['keterangan']['data'];
                $bcp = $em['bcp']['data'];

                // spawn EntryManifest
                $m = new EntryManifest([
                    'koli' => $em['koli'],
                    'brutto' => $em['brutto'],
                    'mawb' => $em['mawb'],
                    'hawb' => $em['hawb'],
                    'nama_importir' => $em['nama_importir'],
                    'alamat_importir' => $em['alamat_importir'],
                ]);
                $m->save();

                // add status
                // append status
                $m->appendStatus(
                    'PENETAPAN', 
                    null, 
                    "Penetapan Sebagai BDN berdasarkan {$p->nomor_lengkap} tanggal {$p->tgl_dok} oleh {$pejabat->name}", 
                    $p
                );

                // add bcp
                $matches = [];
                if (!preg_match('/(BTD|BDN)\-\d{4}\/(\d+)$/i', $bcp['nomor_lengkap'], $matches)) {
                    throw new \Exception("Nomor BCP tidak sesuai format di baris - " . ($total+1));
                }

                $b = new BCP([
                    'nomor_lengkap_dok' => $bcp['nomor_lengkap'],
                    'tgl_dok' => $bcp['tgl_dok'],
                    'no_dok' => $matches[2],
                    'jenis' => 'BDN'
                ]);
                $m->bcp()->save($b);

                // insert keterangan
                foreach ($keterangans as $keterangan) {
                    // save only if it matters
                    if ($keterangan['keterangan']) {
                        $k = new Keterangan($keterangan);
                        $m->keterangan()->save($k);
                    }
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
                $t->lokasi()->associate($lokasi);
                $t->save();

                // assign to penetapan
                $p->entryManifest()->save($m);

                ++$total;
            }

            // return info?
            DB::commit();

            return $this->respondWithArray([
                'inserted' => $total
            ]);
        } catch (PDOException $e) {
            DB::rollBack();

            // return $this->errorBadRequest($e->getMessage());
            return $this->errorBadRequest("Data duplikat pada baris: " . ($total + 1) . "\nDetail: " . $e->getMessage());
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

            // next, update tracking info
            $t = new Tracking();
            $t->lokasi()->associate($tpp);
            $t->petugas()->associate(SSOUserCache::byId($r->userInfo['user_id']));
            $t->trackable()->associate($m);
            $t->save();

            // first, update status
            $m->appendStatus(
                'GATE-IN',
                null,
                "Barang Telah di gate-in oleh {$r->userInfo['username']}",
                $t
            );

            // assign BCP for this entry (IF IT DOESNT HAVE ONE)
            $b = $m->bcp;
            if (!$b) {
                $b = $m->bcp()->create([
                    'kode_kantor' => '050100',
                    'tgl_dok' => date('Y-m-d'),
                    'jenis' => 'BTD'
                ]);
                $b->setNomorDokumen();
            }

            // log it?
            AppLog::logInfo("AWB #{$id} telah di gate-in oleh {$r->userInfo['username']}", $m, false);

            DB::commit();

            // return empty
            return $this->respondWithArray([
                'id' => (int) $m->id,
                'bcp' => $b->nomor_lengkap_dok
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("Entry Manifest #{$id} was not found");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }
}

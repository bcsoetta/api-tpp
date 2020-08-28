<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\BAST;
use App\EntryManifest;
use App\SSOUserCache;
use App\TPS;
use App\Transformers\BASTTransformer;
use App\Transformers\EntryManifestTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BASTController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $r)
    {
        // show em all?
        // query?
        $q = $r->get('q');
        $from = $r->get('from');
        $to = $r->get('to');

        $query = BAST::query()
            ->when($q, function ($query) use ($q) {
                $query->where('nomor_lengkap_dok', 'like', "%$q%")
                    ->orWhereHas('petugas', function ($q2) use ($q) {
                        $q2->where('name', 'like', "%$q%")
                            ->orWhere('nip', 'like', "%$q%");
                    });
            })
            ->when($from, function ($query) use ($from) {
                $query->where('tgl_dok', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->where('tgl_dok', '<=', $to);
            })
            ->latest()
            ->orderBy('id', 'desc');

        $paginator = $query->paginate($r->get('number'))
                            ->appends($r->except('page'));

        return $this->respondWithPagination($paginator, new BASTTransformer);
    }

    /**
     * index all awb of a certain BAST (by id)
     */
    public function indexAwb(Request $r, $id) {
        try {
            $p = BAST::findOrFail($id);

            $q = $r->get('q');
            $from = $r->get('from');
            $to = $r->get('to');

            $query = $p->entryManifest()
                    ->when($q, function ($query) use ($q) {
                        $query->wild($q);
                    })
                    ->when($from, function ($query) use ($from) {
                        $query->from($from);
                    })
                    ->when($to, function ($query) use ($to) {
                        $query->to($to);
                    });
            $paginator = $query->paginate($r->get('number'))
                                ->appends($r->except('page'));
            return $this->respondWithPagination($paginator, new EntryManifestTransformer);
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $r, $kdTps)
    {
        // gotta copy from the other side though
        // store penetapan
        DB::beginTransaction();
        try {
            $tps = TPS::byKode($kdTps)->first();

            if (!$tps) {
                throw new \Exception("TPS {$kdTps} tidak ditemukan");
            }

            // cache petugas_id?
            $petugas_id = $r->userInfo['user_id'];
            SSOUserCache::byId($petugas_id);

            // grab entry_manifest_id
            $entry_manifest_ids = expectSomething($r->get('entry_manifest_id'), 'List AWB');

            if (!is_array($entry_manifest_ids)) {
                throw new \Exception("entry_manifest_id harus berupa array!");
            }

            if (!count($entry_manifest_ids)) {
                throw new \Exception("List AWB tidak boleh kosong!");
            }

            // ok, now we make a new Penetapan
            // $p = new Penetapan([
            //     'kode_kantor'   => '050100',
            //     'nomor_lengkap_dok' => strtoupper(trim($r->get('nomor_lengkap_dok'))),
            //     'tgl_dok' => expectSomething($r->get('tgl_dok'), 'Tanggal Surat Penetapan'),
            //     'pejabat_id' => $pejabat_id
            // ]);
            $b = new BAST([
                'kode_kantor' => '050100',
                'nomor_lengkap_dok' => strtoupper( trim( expectSomething( $r->get('nomor_lengkap_dok'), "Nomor Berita Acara") ) ),
                'tgl_dok' => expectSomething($r->get('tgl_dok'), 'Tanggal Surat Penetapan'),
                'petugas_id' => $petugas_id,
            ]);

            // save it
            $b->save();
            $b->appendStatus('CREATED');

            // if number is empty, assign it
            if (!$b->nomor_lengkap_dok) {
                $b->setNomorDokumen();
            }

            // lock it?
            $b->lock()->create([
                'keterangan' => "BAP untuk BTD dari tps {$kdTps}",
                'petugas_id' => $r->userInfo['user_id']
            ]);
            $b->appendStatus('LOCKED');

            // log it?
            AppLog::logInfo("BAST #{$b->id} direkam oleh {$r->userInfo['username']}", $b, false);

            // now we fill the assignment
            // $ms = $tps->entryManifest()->siapRekamBAST()->get();

            // for each of them, add to penetapan
            foreach ($entry_manifest_ids as $entry_manifest_id) {
                $m = EntryManifest::findOrFail($entry_manifest_id);
                // bail if it's already have a bast
                if ($m->bast()->count()) {
                    throw new \Exception("AWB #{$m->id} '{$m->hawb}' sudah direkam oleh BAST lain!");
                }
                // add to penetapan
                $b->entryManifest()->save($m);

                // append status
                $m->appendStatus(
                    'BAST', 
                    null, 
                    "Berita Acara Serah Terima nomor {$b->nomor_lengkap} tanggal {$b->tgl_dok} telah direkam oleh {$r->userInfo['username']}", 
                    $b
                );
            }

            // commit
            DB::commit();

            // return info on how many was assigned
            return $this->respondWithArray([
                'id' => (int) $b->id,
                'total' => count($entry_manifest_ids),
                'nomor' => $b->nomor_lengkap_dok
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Store a specific amount of awb
     */
    public function storeSpecific(Request $r) {
        // gotta copy from the other side though
        // store penetapan
        DB::beginTransaction();
        try {
            // cache petugas_id?
            $petugas_id = $r->userInfo['user_id'];
            SSOUserCache::byId($petugas_id);

            // ok, now we make a new Penetapan
            // $p = new Penetapan([
            //     'kode_kantor'   => '050100',
            //     'nomor_lengkap_dok' => strtoupper(trim($r->get('nomor_lengkap_dok'))),
            //     'tgl_dok' => expectSomething($r->get('tgl_dok'), 'Tanggal Surat Penetapan'),
            //     'pejabat_id' => $pejabat_id
            // ]);
            $nomor_lengkap_dok = strtoupper(trim(expectSomething($r->get('nomor_lengkap_dok'), 'Nomor BAST') ));
            $tgl_dok = expectSomething($r->get('tgl_dok'), 'Tanggal Surat Penetapan');

            $b = new BAST([
                'kode_kantor' => '050100',
                'nomor_lengkap_dok' => $nomor_lengkap_dok,
                'tgl_dok' => $tgl_dok,
                'petugas_id' => $petugas_id,
                'ex_p2' => $r->get('ex_p2',false)
            ]);

            // save it
            $b->save();
            $b->appendStatus('CREATED');

            // if number is empty, assign it
            if (!$b->nomor_lengkap_dok) {
                $b->setNomorDokumen();
            }

            // lock it?
            $b->lock()->create([
                'keterangan' => "BAST barang KEP-BDN",
                'petugas_id' => $r->userInfo['user_id']
            ]);
            $b->appendStatus('LOCKED');

            // log it?
            AppLog::logInfo("BAST #{$b->id} direkam oleh {$r->userInfo['username']}", $b, false);

            // now we fill the assignment
            $ids = expectSomething($r->get('entry_manifest', []), 'Entry Manifest');
            $ms = EntryManifest::findOrFail($ids);

            // for each of them, add to penetapan
            foreach ($ms as $m) {
                // add to penetapan
                $b->entryManifest()->save($m);

                // append status
                $m->appendStatus(
                    'BAST', 
                    null, 
                    "Berita Acara Serah Terima nomor {$b->nomor_lengkap} tanggal {$b->tgl_dok} telah direkam oleh {$r->userInfo['username']}", 
                    $b
                );
            }

            // commit
            DB::commit();

            // return info on how many was assigned
            return $this->respondWithArray([
                'id' => (int) $b->id,
                'total' => count($ms),
                'nomor' => $b->nomor_lengkap_dok
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

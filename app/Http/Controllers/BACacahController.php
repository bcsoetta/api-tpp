<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\BACacah;
use App\BAST;
use App\EntryManifest;
use App\Penetapan;
use App\SSOUserCache;
use App\Transformers\BACacahTransformer;
use App\Transformers\EntryManifestTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BACacahController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $r)
    {
        // list all ba cacah
        $q = $r->get('q');
        $from = $r->get('from');
        $to = $r->get('to');

        $query = BACacah::query()
                ->when($q, function ($q1) use ($q) {
                    $q1->wild($q)
                        ->orWhere(function ($q2) use ($q) {
                            $q2->byPejabat($q);
                        });
                })
                ->when($from, function ($q1) use ($from) {
                    $q1->where('tgl_dok', '>=', $from);
                })
                ->when($to, function ($q1) use ($to) {
                    $q1->where('tgl_dok', '<=', $to);
                })
                ->latest()
                ->orderBy('id', 'desc');

        $paginator = $query->paginate($r->get('number'))
                            ->appends($r->except('page'));

        return $this->respondWithPagination($paginator, new BACacahTransformer);
    }

    /**
     * index all awb of a certain BAST (by id)
     */
    public function indexAwb(Request $r, $id) {
        try {
            $p = BACacah::findOrFail($id);

            $q = $r->get('q');
            $from = $r->get('from');
            $to = $r->get('to');

            $orderBy = $r->get('orderBy');

            $query = $p->entryManifest()
                    ->when($q, function ($query) use ($q) {
                        $query->wild($q);
                    })
                    ->when($from, function ($query) use ($from) {
                        $query->from($from);
                    })
                    ->when($to, function ($query) use ($to) {
                        $query->to($to);
                    })
                    ->when($orderBy, function ($query) use ($orderBy) {
                        $orders = explode(',', $orderBy);
                        $query->select('entry_manifest.*');
                        foreach ($orders as $ord) {
                            $ord = explode('|', $ord);
                            if ($ord[0] == 'bcp') {
                                $query->with('bcp')->leftJoin('bcp', 'bcp.entry_manifest_id', '=', 'entry_manifest.id')
                                    ->orderBy('bcp.nomor_lengkap_dok', $ord[1])
                                ;
                            }
                        }
                    })
                    ;
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
    public function store(Request $r)
    {
        // careful here, big function!!
        // use transaction
        DB::beginTransaction();

        try {
            //read all required data
            $nomor_lengkap = expectSomething($r->get('nomor_lengkap'), 'Nomor Lengkap BA Cacah');
            $tgl_dok = expectSomething($r->get('tgl_dok'), 'Tanggal BA Cacah');
            
            // $nomor_st = expectSomething($r->get('nomor_st'), 'Nomor Surat Tugas');
            // $tgl_st = expectSomething($r->get('tgl_st'), 'Tanggal Surat Tugas');          

            $pejabat_id = expectSomething($r->get('pejabat_id'), 'Pejabat Yang Mengetahui');

            $penetapan_id = expectSomething($r->get('penetapan_id'), 'Dasar Penetapan');
            $bast_id = expectSomething($r->get('bast_id'), 'Dasar BA Serah Terima/BA Penarikan');
            $pelaksana_id = expectSomething($r->get('pelaksana_id'), 'Pelaksana yang melakukan Pencacahan');
            
            $entry_manifest_id = expectSomething($r->get('entry_manifest_id'), 'Lampiran Entry Manifest (AWB)');

            // caching data pejabat
            $pejabat = SSOUserCache::byId($pejabat_id);
            
            // safe to spawn data here...
            $b = new BACacah([
                'nomor_lengkap_dok' => $nomor_lengkap,
                'tgl_dok' => $tgl_dok,

                /* 'nomor_st' => $nomor_st,
                'tgl_st' => $tgl_st, */

                'pejabat_id' => $pejabat->user_id
            ]);

            // save and log?
            $b->save();
            AppLog::logInfo("BA Cacah #{$b->id} direkam oleh {$r->userInfo['username']}", $b, false);

            // append all details
            // Detail Penetapan
            foreach ($penetapan_id as $pid) {
                $p = Penetapan::findOrFail($pid);

                // append it
                $b->penetapan()->save($p);
            }

            // Detail BAST
            foreach ($bast_id as $bid) {
                $ba = BAST::findOrFail($bid);

                // append
                $b->bast()->save($ba);
            }

            // Detail Pelaksana
            foreach ($pelaksana_id as $pid) {
                $p = SSOUserCache::byId($pid);

                // append
                $b->pelaksana()->save($p);
            }

            // Detail EntryManifest
            $rowPos = 1;    // current row id
            $lastType = null;   // last bcp type

            foreach ($entry_manifest_id as $mid) {
                $m = EntryManifest::findOrFail($mid);
                // fail if it's got a ba cacah already
                if (count($m->baCacah)) {
                    throw new \Exception("AWB {$m->hawb} udah pernah direkam ba cacahnya di BA Nomor {$m->baCacah[0]->nomor_lengkap_dok} tanggal {$m->baCacah[0]->tgl_dok}!");
                }

                // if we recorded last type, and our type is dissimilar
                if ($lastType && $m->bcp->jenis != $lastType) {
                    // different shit!! tell em
                    throw new \Exception("Jenis BCP berbeda di baris {$rowPos}, sebelumnya: {$lastType}, beda dengan {$m->bcp->jenis}");
                }

                // go on,
                ++$rowPos;
                $lastType = $m->bcp->jenis;

                // good we're save to continue
                $b->entryManifest()->save($m);

                // update status
                $m->appendStatus(
                    'REKAM BA-CACAH',
                    null,
                    "Perekaman BA Cacah dilakukan oleh {$r->userInfo['username']} dengan nomor {$b->nomor_lengkap_dok} tanggal {$b->tgl_dok}",
                    $b
                );

                // kunci pencacahan
                $m->pencacahan->lock()->create([
                    'keterangan' => "Dikunci dengan BA Cacah #{$b->id}, nomor {$b->nomor_lengkap_dok} tanggal {$b->tgl_dok}",
                    'petugas_id' => $r->userInfo['user_id']
                ]);

                // log it?
                AppLog::logInfo("Entry Manifest #{$m->id} direkam ba cacahnya dengan BACacah #{$b->id} oleh {$r->userInfo['username']}", $m, false);
            }
            
            // commit
            DB::commit();

            // return something?
            return $this->respondWithArray([
                'id' => (int) $b->id,
                'uri' => $b->uri,
                'nomor_lengkap' => $b->nomor_lengkap_dok,
                'tgl_dok' => $b->tgl_dok,
                'total' => count($entry_manifest_id)
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
        try {
            $b = BACacah::findOrFail($id);

            return $this->respondWithItem($b, new BACacahTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("BACacah #{$id} was not found");
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
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
        // can't think of when it's appropriate... hmmm.
    }
}

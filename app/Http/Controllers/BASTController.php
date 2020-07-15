<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\BAST;
use App\SSOUserCache;
use App\TPS;
use App\Transformers\BASTTransformer;
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

            // ok, now we make a new Penetapan
            // $p = new Penetapan([
            //     'kode_kantor'   => '050100',
            //     'nomor_lengkap_dok' => strtoupper(trim($r->get('nomor_lengkap_dok'))),
            //     'tgl_dok' => expectSomething($r->get('tgl_dok'), 'Tanggal Surat Penetapan'),
            //     'pejabat_id' => $pejabat_id
            // ]);
            $b = new BAST([
                'kode_kantor' => '050100',
                'nomor_lengkap_dok' => strtoupper(trim($r->get('nomor_lengkap_dok'))),
                'tgl_dok' => expectSomething($r->get('tgl_dok'), 'Tanggal Surat Penetapan'),
                'petugas_id' => $petugas_id
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
                'keterangan' => "Penetapan BTD untuk tps {$kdTps}",
                'petugas_id' => $r->userInfo['user_id']
            ]);
            $b->appendStatus('LOCKED');

            // log it?
            AppLog::logInfo("BAST #{$b->id} direkam oleh {$r->userInfo['username']}", $b, false);

            // now we fill the assignment
            $ms = $tps->entryManifest()->siapRekamBAST()->get();

            // for each of them, add to penetapan
            foreach ($ms as $m) {
                // add to penetapan
                $b->entryManifest()->save($m);

                // append status
                $m->appendStatus('BAST', null, null, $b);
            }

            // commit
            DB::commit();

            // return info on how many was assigned
            return $this->respondWithArray([
                'id' => (int) $b->id,
                'total' => count($ms)
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

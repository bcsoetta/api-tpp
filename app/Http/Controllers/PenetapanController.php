<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\Penetapan;
use App\SSOUserCache;
use App\TPS;
use App\Transformers\PenetapanTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenetapanController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $r)
    {
        // query?
        $q = $r->get('q');

        $query = Penetapan::query()
            ->when($q, function ($query) use ($q) {
                $query->where('nomor_lengkap_dok', 'like', "%$q%")
                    ->orWhereHas('pejabat', function ($q2) use ($q) {
                        $q2->where('nama', 'like', "%$q%")
                            ->orWhere('nip', 'like', "%$q%");
                    });
            })
            ->latest()
            ->orderBy('id', 'desc');

        $paginator = $query->paginate($r->get('number'))
                            ->appends($r->except('page'));

        return $this->respondWithPagination($paginator, new PenetapanTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $r, $kdTps)
    {
        // store penetapan
        DB::beginTransaction();
        try {
            $tps = TPS::byKode($kdTps)->first();

            if (!$tps) {
                throw new \Exception("TPS {$kdTps} tidak ditemukan");
            }

            // cache pejabat_id
            $pejabat_id = expectSomething($r->get('pejabat_id'), "Pejabat Penetapan");

            SSOUserCache::byId($pejabat_id);

            // ok, now we make a new Penetapan
            $p = new Penetapan([
                'kode_kantor'   => '050100',
                'nomor_lengkap_dok' => strtoupper(trim($r->get('nomor_lengkap_dok'))),
                'tgl_dok' => expectSomething($r->get('tgl_dok'), 'Tanggal Surat Penetapan'),
                'pejabat_id' => $pejabat_id
            ]);

            // save it
            $p->save();
            $p->appendStatus('CREATED');

            // if number is empty, assign it
            if (!$p->nomor_lengkap_dok) {
                $p->setNomorDokumen();
            }

            // lock it?
            $p->lock()->create([
                'keterangan' => "Penetapan BTD untuk tps {$kdTps}",
                'petugas_id' => $r->userInfo['user_id']
            ]);
            $p->appendStatus('LOCKED');

            // log it?
            AppLog::logInfo("Penetapan #{$p->id} direkam oleh {$r->userInfo['username']}", $p, false);

            // now we fill the assignment
            $ms = $tps->entryManifest()->siapPenetapan()->get();

            // for each of them, add to penetapan
            foreach ($ms as $m) {
                // add to penetapan
                $p->entryManifest()->save($m);

                // append status
                $m->appendStatus('PENETAPAN', null, null, $p);
            }

            // commit
            DB::commit();

            // return info on how many was assigned
            return $this->respondWithArray([
                'id' => (int) $p->id,
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

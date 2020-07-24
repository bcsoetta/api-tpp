<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\EntryManifest;
use App\Penetapan;
use App\SSOUserCache;
use App\TPS;
use App\Transformers\EntryManifestTransformer;
use App\Transformers\PenetapanTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $from = $r->get('from');
        $to = $r->get('to');

        $query = Penetapan::query()
            ->when($q, function ($query) use ($q) {
                $query->where('nomor_lengkap_dok', 'like', "%$q%")
                    ->orWhereHas('pejabat', function ($q2) use ($q) {
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

        return $this->respondWithPagination($paginator, new PenetapanTransformer);
    }

    /**
     * indexAwb
     * list all awb belong to this penetapan
     */
    public function indexAwb(Request $r, $id) {
        try {
            $p = Penetapan::findOrFail($id);

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
        // store penetapan
        DB::beginTransaction();
        try {
            $kdTps = explode(',', $kdTps);
            
            if (TPS::byKode($kdTps)->count() != count($kdTps)) {
                throw new \Exception("Salah satu kode TPS tidak valid!");
            }

            // cache pejabat_id
            $pejabat_id = expectSomething($r->get('pejabat_id'), "Pejabat Penetapan");

            $pejabat = SSOUserCache::byId($pejabat_id);

            // grab nomor_lengkap_dok
            $nomor_surat = trim($r->get('nomor_lengkap_dok')); // strtoupper( trim( expectSomething( $r->get('nomor_lengkap_dok'), 'Nomor Surat Penetapan (Cek Nadine)') ) );

            // if not set
            if (!$nomor_surat) {
                $nomor_surat = "<PENETAPAN - " . str_pad( getSequence("PENETAPAN", date('Y')), 5, "0", STR_PAD_LEFT) . ">";
            } else {
                $nomor_surat = strtoupper($nomor_surat);
            }

            // ok, now we make a new Penetapan
            $p = new Penetapan([
                'kode_kantor'   => '050100',
                'nomor_lengkap_dok' => $nomor_surat,
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
            $flatKodeTps = implode(',', $kdTps);
            $p->lock()->create([
                'keterangan' => "Penetapan BTD untuk tps {$flatKodeTps}",
                'petugas_id' => $r->userInfo['user_id']
            ]);
            $p->appendStatus('LOCKED');

            // log it?
            AppLog::logInfo("Penetapan #{$p->id} direkam oleh {$r->userInfo['username']}", $p, false);

            // now we fill the assignment
            $ms = EntryManifest::siapPenetapan()->whereHas('tps', function ($q) use ($kdTps) {
                $q->byKode($kdTps);
            })->get();

            // for each of them, add to penetapan
            foreach ($ms as $m) {
                // add to penetapan
                $p->entryManifest()->save($m);

                // append status
                $m->appendStatus(
                    'PENETAPAN', 
                    null, 
                    "Penetapan Sebagai BTD berdasarkan {$p->nomor_lengkap} tanggal {$p->tgl_dok} oleh {$pejabat->name}", 
                    $p
                );
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
            return $this->errorBadRequest($e->getMessage() /* . " @ line " . $e->getLine() . ' of '. $e->getFile() */);
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
    public function updateSuratPenetapan(Request $r, $id)
    {
        DB::beginTransaction();
        // update surat penetapan aja
        try {
            $p = Penetapan::findOrFail($id);

            // grab data
            $nomor_surat = expectSomething(strtoupper(trim($r->get('nomor_lengkap_dok') ?? $r->get('nomor_lengkap'))), 'Nomor Surat Penetapan');
            $tgl_surat = expectSomething($r->get('tgl_dok'), "Tanggal Surat Penetapan");

            // let's force them to be serious
            if (!preg_match('/^(S|KEP)-\d+/i', $nomor_surat)) {
                throw new \Exception("Nomor Surat tidak sesuai skema Surat/KEP penetapan BTD/BMN");
            }

            // safe to continue
            $p->nomor_lengkap_dok = $nomor_surat;
            $p->tgl_dok = $tgl_surat;

            $p->save();

            // log it?
            AppLog::logInfo("Penetapan #{$id} diedit nomor suratnya oleh {$r->userInfo['username']}", $p, true);

            // just tell em it's good
            DB::commit();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("Penetapan #{$id} was not found");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
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

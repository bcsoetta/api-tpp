<?php

namespace App\Http\Controllers;

use App\TPS;
use App\Transformers\EntryManifestTransformer;
use App\Transformers\TPSTransformer;
use Highlight\Mode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TPSController extends ApiController
{
    public function index(Request $r) {
        $q = $r->get('q');

        $query = TPS::query();
        
        // what if it's special query?
        if ($r->get('siap_penetapan') == true) {
            $query = TPS::siapPenetapan();
        } else if ($r->get('siap_rekam_bast') == true) {
            $query = TPS::siapRekamBAST();
        }

        $query = $query->when($q, function ($query) use ($q) {
            $query->where('kode', 'like', "%$q%")
                ->orWhere('nama', 'like', "%$q%");
        });

        $show_all = $r->get('show_all');

        $paginator = $query->paginate($show_all ? $query->count() : $r->get('number', 10))
                        ->appends($r->except('page'));
        
        return $this->respondWithPagination($paginator, new TPSTransformer);
    }

    public function showByKode(Request $r, $kode) {
        try {
            $t = TPS::byKode($kode)->first();

            if (!$t) {
                throw new ModelNotFoundException("TPS dengan kode '$kode' tidak ditemukan");
            }

            return $this->respondWithItem($t, new TPSTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function store(Request $r) {
        try {
            // check for used kode
            if ( TPS::byKode($r->get('kode'))->count() ) {
                throw new \Exception("Kode TPS Sudah Pernah Dgunakan !");
            }
            
            // just create it
            $t = new TPS([
                'kode' => expectSomething($r->get('kode'), "Kode TPS"),
                'nama' => expectSomething($r->get('nama'), "Nama TPS"),
                'alamat' => $r->get('alamat'),
                'kode_kantor' => $r->get('kode_kantor') ?? '050100'
            ]);

            $t->save();

            return $this->respondWithArray([
                'id' => (int) $t->id,
                'kode' => $t->kode,
                'uri' => $t->kode
            ]);
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function update(Request $r, $id) {
        try {
            $t = TPS::findOrFail($id);

            $t->kode = expectSomething($r->get('kode'), 'Kode TPS');
            $t->nama = expectSomething($r->get('nama'), 'Nama TPS');
            $t->alamat = $r->get('alamat', '') ?? "";

            $t->save();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("TPS #{$id} was not found");
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function destroy(Request $r, $id) {
        try {
            TPS::findOrFail($id)->delete();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound("TPS #{$id} was not found");
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function indexAwbSiapPenetapan(Request $r, $kode) {
        try {
            // what is it?
            $t = TPS::byKode($kode)->first();

            if (!$t) {
                throw new ModelNotFoundException("TPS $kode was not found!");
            }

            // index all that is not yet defined
            $query = $t->entryManifest()->siapPenetapan();

            $paginator = $query->paginate($r->get('number', 10))
                                ->appends($r->except('page'));
            
            return $this->respondWithPagination($paginator, new EntryManifestTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function indexAwbSiapRekamBAST(Request $r, $kode) {
        try {
            // what is it?
            $t = TPS::byKode($kode)->first();

            if (!$t) {
                throw new ModelNotFoundException("TPS $kode was not found!");
            }

            // index all that is not yet defined
            $query = $t->entryManifest()->siapRekamBAST();

            $paginator = $query->paginate($r->get('number', 10))
                                ->appends($r->except('page'));
            
            return $this->respondWithPagination($paginator, new EntryManifestTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}

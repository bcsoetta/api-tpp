<?php

namespace App\Http\Controllers;

use App\TPS;
use App\Transformers\TPSTransformer;
use Highlight\Mode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TPSController extends ApiController
{
    public function index(Request $r) {
        $q = $r->get('q');

        $query = TPS::query()
                ->when($q, function ($query) use ($q) {
                    $query->where('kode', 'like', "%$q%")
                        ->orWhere('nama', 'like', "%$q%");
                });
        
        // what if it's special query?
        if ($r->get('siap_penetapan') == true) {
            $query = TPS::siapPenetapan();
        }

        $paginator = $query->paginate($r->get('number', 10))
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
}

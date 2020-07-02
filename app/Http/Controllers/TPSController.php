<?php

namespace App\Http\Controllers;

use App\TPS;
use App\Transformers\TPSTransformer;
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
}

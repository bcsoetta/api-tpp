<?php

namespace App\Http\Controllers;

use App\Rack;
use App\Transformers\RackTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDOException;

class RackController extends ApiController
{
    // show all rack data?
    public function index(Request $r) {

        $q = $r->get('q');
        $show_all = $r->get('show_all');

        $query = Rack::query()
        ->when($q, function ($q1) use ($q) {
            $q1->byKode($q);
        })
        ->orderBy('kode', 'asc');

        $number = $show_all ? $query->count() : $r->get('number', 10);

        $paginator = $query->paginate($number)
                            ->appends($r->except('page'));
        return $this->respondWithPagination($paginator, new RackTransformer);
    }

    // show specific rack data?
    public function show($id) {
        try {
            // find it
            $r = Rack::findOrFail($id);

            return $this->respondWithItem($r, new RackTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function showByKode($kode) {
        try {
            // grab by kode
            $r = Rack::byKode($kode)->first();
            if (!$r) {
                throw new ModelNotFoundException("Rack dengan kode '{$kode}' tidak ditemukan!");
            }
            return $this->respondWithItem($r, new RackTransformer);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound($e->getMessage());
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // store new rack data
    public function store(Request $r) {
        try {
            // grab necessary data
            $k = expectSomething($r->get('kode'), 'Kode Rak');

            $rack = new Rack([
                'kode' => $k,
                'x' => $r->get('x',0.0),
                'y' => $r->get('y',0.0),
                'w' => $r->get('w',0.0),
                'h' => $r->get('h',0.0),
                'rot' => $r->get('rot',0.0)
            ]);
            $rack->save();

            return $this->respondWithItem($rack, new RackTransformer);
        } catch (PDOException $e) {
            return $this->errorBadRequest("Kode rak double!");
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // update rack data?
    public function update(Request $r, $id) {
        DB::beginTransaction();
        try {
            $rack = Rack::findOrFail($id);

            // do update here
            $rack->kode = $r->get('kode', $rack->kode);
            $rack->x = $r->get('x', $rack->x);
            $rack->y = $r->get('y', $rack->y);
            $rack->w = $r->get('w', $rack->w);
            $rack->h = $r->get('h', $rack->h);
            $rack->rot = $r->get('rot', $rack->rot);
            
            $rack->save();

            DB::commit();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // delete
    public function delete($id) {
        try {
            $rack = Rack::findOrFail($id);
            $rack->delete();

            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}

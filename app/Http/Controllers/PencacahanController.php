<?php

namespace App\Http\Controllers;

use App\AppLog;
use App\DetailBarang;
use App\EntryManifest;
use App\Pencacahan;
use App\SSOUserCache;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PencacahanController extends ApiController
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function createOrUpdate(Request $r, $id)
    {
        DB::beginTransaction();
        // will create if none exist, update otherwise
        try {
            // manifest must exist though
            $m = EntryManifest::findOrFail($id);

            // create or update
            $p = $m->pencacahan;

            if (!$p) {
                // create it, less strict
                $p = new Pencacahan([
                    'kondisi_barang' => $r->get('kondisi_barang', 'Baik'),
                    'peruntukan_awal' => $r->get('peruntukan_awal', 'DILELANG')
                ]);
                $p->entryManifest()->associate($m);

                // save it and return 204
                $p->save();
            } else {
                // update it
                // if it's locked, throw error
                if ($p->is_locked) {
                    throw new \Exception("Data Pencacahan sudah terkunci!");
                }

                // go on
                $p->kondisi_barang = expectSomething($r->get('kondisi_barang'), 'Kondisi Barang');
                $p->peruntukan_awal = expectSomething($r->get('peruntukan_awal'), 'Peruntukan Awal');

                $p->save();

                // sync detailBarang here?
                $barang = $r->get('barang')['data'];

                // grab all good ids
                $toSync = array_filter($barang, function($e) { return $e['id'] > 0; });
                $toInsert = array_filter($barang, function($e) { return !$e['id']; });

                // remove all unneeded shit first
                $toSyncIds = array_map(function($e){ return $e['id']; }, $toSync);
                $p->detailBarang()->whereNotIn('id', $toSyncIds)->delete();

                // update available shits
                foreach ($toSync as $item) {
                    $d = DetailBarang::find($item['id']);
                    if (!$d) {
                        throw new \Exception("Detail barang #{$item['id']} tidak valid!");
                    }

                    // go on, save it
                    $d->jenis = $item['jenis'];
                    $d->jumlah = $item['jumlah'];
                    $d->uraian = $item['uraian'];
                    $d->save();
                }

                // add new shit
                foreach ($toInsert as $item) {
                    if (!$item['uraian']) {
                        throw new \Exception("Uraian Barang Detil Pencacahan tidak boleh kosong!");
                    }
                    
                    $p->detailBarang()->create($item);
                }

                // check if it's empty
                if (!$p->detailBarang()->count()) {
                    throw new \Exception("Detail barang pada pencacahan tidak boleh kosong!");
                }

                // throw new \Exception("To Sync: " . count($toSync). ", To Insert: " . count($toInsert));
            }            

            DB::commit();

            // return empty
            return $this->setStatusCode(204)
                        ->respondWithEmptyBody();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorNotFound("EntryManifest #{$id} was not found");
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

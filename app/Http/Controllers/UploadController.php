<?php

namespace App\Http\Controllers;

use App\EntryManifest;
use App\Lampiran;
use App\Pencacahan;
use App\PNBP;
use App\Transformers\LampiranTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDOException;

class UploadController extends ApiController
{
    private static $mapping = [
        'awb' => EntryManifest::class,
        'hawb' => EntryManifest::class,
        'pencacahan' => Pencacahan::class,
        'pnbp' => PNBP::class
    ];

    // private method to instance shiet?
    protected function instantiate($doctype, $docid) {
        // find a mapping
        if (!key_exists($doctype, UploadController::$mapping)) {
            throw new \Exception("'{$doctype}' was not supported for uploading!");
        }
        $classname = UploadController::$mapping[$doctype];

        // instantiate
        $master = $classname::findOrFail($docid);

        // does it support lampiran?
        if (!method_exists($master, 'lampiran')) {
            throw new \Exception("This thing doesnt support uploading!");
        }

        // handle the file upload
        // unless it's locked
        /* if ($master->is_locked) {
            throw new \Exception("Can't upload cause data is locked already!");
        } */

        return $master;
    }

    // handle upload
    public function handleUpload(Request $r, $doctype, $docid) {
        try {
            $master = $this->instantiate($doctype, $docid);

            // read all data
            $body = $r->getContent();

            $filename = $r->header('X-Content-Filename');
            $filesize = $r->header('X-Content-Filesize');
            $filetype   = $r->header('Content-Type');

            // jenis file?
            $jenis_file = 'LAIN-LAIN';
            if (preg_match("/^image\/.*/i", $filetype)) {
                $jenis_file = "GAMBAR";
            } else if (preg_match("/^application\/pdf$/i", $filetype)) {
                $jenis_file = "DOKUMEN";
            }

            // parse base64 data
            $base64_data = explode(',', $body);

            // for now, just store it somewhere
            $uniqueFilename = uniqid() . Str::random() . $filename;
            Storage::disk('public')->put($uniqueFilename, base64_decode($base64_data[1]) );

            // generate lampiran object
            $l  = new Lampiran([
                'resumable_upload_id'   => '-',
                'jenis'                 => $jenis_file,
                'mime_type'             => $filetype,
                'filename'              => $filename,
                'filesize'              => $filesize,
                'diskfilename'          => $uniqueFilename,
                'blob'                  => $base64_data[1]
            ]);

            // attach it
            $master->lampiran()->save($l);

            return $this->respondWithItem($l, new LampiranTransformer);
        } catch(PDOException $e) {
            return $this->errorBadRequest("File might be too large, max allowed is 16 MB");
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function getAttachments(Request $r, $doctype, $docid) {
        try {
            $master = $this->instantiate($doctype, $docid);

            return $this->respondWithCollection($master->lampiran, new LampiranTransformer);
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    // delete specific lampiran
    public function deleteAttachment(Request $r, $id) {
        $l = Lampiran::find($id);

        if (!$l) {
            return $this->errorNotFound("Lampiran #{$id} was not found.");
        }

        try {
            // attempt deletion here
            // first, make sure if we have parent
            if (!$l->Attachable) {
                // no parent, safe to delete
                $l->delete();

                return $this->setStatusCode(204)
                            ->respondWithEmptyBody();
            } else {
                // welp, we have parent. check if we're locked
                if ($l->Attachable->is_locked) {
                    throw new \Exception("Can't do that, our parent document is locked already!");
                }

                // safe to delete
                $l->delete();
                
                return $this->setStatusCode(204)
                            ->respondWithEmptyBody();
            }
        } catch (\Exception $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }
}

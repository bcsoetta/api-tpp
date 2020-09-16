<?php
namespace App\Transformers;

use App\Rack;
use League\Fractal\TransformerAbstract;

class RackTransformer extends TransformerAbstract {

    public function transform(Rack $r) {
        return [
            'id' => (int) $r->id,
            'kode' => (string) $r->kode,
            'x' => (float) $r->x,
            'y' => (float) $r->y,
            'w' => (float) $r->w,
            'h' => (float) $r->h,
            'rot' => (float) $r->rot,

            'created_at' => (string) $r->created_at,
            'updated_at' => (string) $r->updated_at,
        ];
    }
}
<?php
namespace App\Transformers;

use App\SSOUserCache;
use League\Fractal\TransformerAbstract;

class SSOUserCacheTransformer extends TransformerAbstract {
    public function transform(SSOUserCache $u) {
        // return $u->toArray();
        return [
            'user_id' => (int) $u->user_id,
            'username' => $u->username,
            'name' => $u->name,
            'nip' => $u->nip,
            'pangkat' => $u->pangkat,
            'status' => $u->status
        ];
    }
}
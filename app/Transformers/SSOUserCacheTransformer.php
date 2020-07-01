<?php
namespace App\Transformers;

use App\SSOUserCache;
use League\Fractal\TransformerAbstract;

class SSOUserCacheTransformer extends TransformerAbstract {
    public function transform(SSOUserCache $u) {
        return $u->toArray();
    }
}
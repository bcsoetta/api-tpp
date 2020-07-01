<?php
namespace App\Transformers;

use App\Status;
use League\Fractal\TransformerAbstract;

class StatusTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'detail'
    ];

    protected $defaultIncludes = [
        'detail'
    ];

    public function transform(Status $status) {
       
        return [
            'id'    => (int) $status->id,
            'status' => $status->status,
            'created_at' => (string) $status->created_at
        ];
    }

    public function includeDetail(Status $s) {
        if ($s->detail)
            return $this->item($s->detail, new StatusDetailTransformer);
    }
}


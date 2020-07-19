<?php
namespace App\Transformers;

use App\StatusDetail;
use League\Fractal\TransformerAbstract;

class StatusDetailTransformer extends TransformerAbstract {
    // available links?
    protected $availableIncludes = [
        'linkable'
    ];

    public function transform(StatusDetail $s) {
        $ret = [
            'id'    => (int) $s->id,
            'linkable_type' => $s->linkable_type,
            'linkable_id'   => $s->linkable_id,
            'keterangan'    => $s->keterangan,
            'other_data'    => $s->other_data,
            'created_at'    => (string) $s->created_at,
            'updated_at'    => (string) $s->updated_at
        ];

        // SPECIAL PROCESSING HERE...e,g for downloadable or something

        return $ret;
    }

    public function includeLinkable(StatusDetail $s) {
        $l = $s->linkable;

        if ($l) {
            
            // what is our linkable type?
            $classname = class_basename($l);
            $transformerName = 'App\\Transformers\\'. $classname . 'Transformer';

            // does it exist?
            if (!class_exists($transformerName)) {
                throw new \Exception("Transformer  '{$transformerName}' does not exist!");
            }

            // it exists!! so just spawn it
            return $this->item($l, new $transformerName);
        }
    }
}
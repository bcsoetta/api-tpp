<?php
namespace App\Transformers;

use App\Setting;
use League\Fractal\TransformerAbstract;

class SettingTransformer extends TransformerAbstract {
    public function transform(Setting $s) {
        $realValue = Setting::getValue($s->key);

        return [
            'key' => (string) $s->key,
            'value' => $realValue,
            'string_value' => $s->value,
            'type' => $s->type
        ];
    }
}
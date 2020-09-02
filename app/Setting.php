<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // setting
    protected $table = 'settings';

    protected $guarded = [
        'updated_at',
        'created_at'
    ];

    static public function getValue($key) {
        $s = Setting::where('key', $key)->first();
        if (!$s) {
            return null;
        }

        try {
            switch ($s->type) {
                case 'NUMBER':
                    return (float) $s->value;
                case 'STRING':
                    return (string) $s->value;
                case 'ARRAY':
                    return explode(',', $s->value);
            }
            return $s->value;
        } catch (\Throwable $e) {
            return null;
        }     
    }

    static public function setValue($key, $value, $type='STRING') {
        try {
            $s = Setting::where('key', $key)->first();
            if (!$s) {
                $s = new Setting;
            }
            $s->key = $key;
            $s->value = is_array($value) ? implode(',', $value) : $value;
            $s->type = $s->type ?? $type;
            $s->save();

            return true;
        } catch (\Throwable $e) {
            return false;
        }
        
    }


}

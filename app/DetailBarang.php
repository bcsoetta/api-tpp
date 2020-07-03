<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailBarang extends Model
{
    // settings
    protected $table = 'detail_barang';

    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'uraian' => '',
        'jumlah' => null,
        'jenis' => null
    ];

    // relations 
    public function header() {
        return $this->morphTo();
    }

    // static helper
    public static function fromString($str) {
        // trim that shit first
        $str = trim($str);

        if (preg_match('/(\d{1,4})\s+(\w{1,8})\s+(.+)/si', $str, $matches) && count($matches) > 3) {
            // 1st: jumlah, 2nd: jenis, 3rd: uraian
            return new DetailBarang([
                'jumlah' => (float) $matches[1],
                'jenis' => (string) strtoupper($matches[2]),
                'uraian' => (string) $matches[3]
            ]);
        }
        // otherwise, return usual stuffs
        return new DetailBarang(['uraian' => $str]);
    }
}

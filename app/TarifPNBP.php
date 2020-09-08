<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TarifPNBP extends Model
{
    use SoftDeletes;
    // table settings
    protected $table = 'tarif_pnbp';
    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}

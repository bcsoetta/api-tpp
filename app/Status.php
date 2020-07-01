<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Status extends Model
{
    // table name
    protected $table = 'status';


    protected $fillable = ['status', 'lokasi'];

    protected $attributes = [
        'statusable_id' => 0,
        'statusable_type' => ''
    ];

    public function statusable(){
        return $this->morphTo();
    }

    public function detail(){
        return $this->hasOne('App\StatusDetail', 'status_id', 'id');
    }

    public function scopeByDoctype($query, $doctype) {
        return $query->where('statusable_type', $doctype);
    }

    public function scopeLatestPerDoctype($query, $timerange=null) {
        return $query->latest()
                    ->join(
                        DB::raw("
                        (SELECT
                            statusable_id sid,
                            statusable_type stype,
                            MAX(id) maxid
                        FROM
                            `status`
                        GROUP BY
                            statusable_id,
                            statusable_type
                        ) stat
                        "),
                        function ($join) {
                            $join->on('status.statusable_id', '=', 'stat.sid');
                            $join->on('status.statusable_type', '=', 'stat.stype');
                            $join->on('status.id', '=', 'stat.maxid');
                        }
                    );
    }

    public function scopeByStatus($query, $status) {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }
        return $query->where('status', '=', $status);
    }

    public function scopeByStatusOtherThan($query, $status) {
        if (is_array($status)) {
            return $query->whereNotIn('status', $status);
        }
        return $query->where('status', '<>', $status);
    }
}

<?php

use App\Lokasi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLokasiGateOutToLokasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lokasi', function (Blueprint $table) {
            //
        });

        // just add it here
        $l = new Lokasi([
            'kode' => 'GATEOUT',
            'nama' => 'Gate Out dari TPP Soetta'
        ]);
        $l->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lokasi', function (Blueprint $table) {
            // delete
            Lokasi::byKode('GATEOUT')->first()->delete();
        });
    }
}

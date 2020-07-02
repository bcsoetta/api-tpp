<?php

use App\Lokasi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeedLokasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Just insert a KNOWN new location
        Lokasi::create([
            'kode' => 'P2SH',
            'deskripsi' => 'Gudang penyimpanan barang SBP P2 Soetta'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

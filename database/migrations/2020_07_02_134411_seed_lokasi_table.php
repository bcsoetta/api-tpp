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
            'nama' => 'Gudang penyimpanan barang SBP P2 Soetta'
        ]);

        Lokasi::create([
            'kode' => 'TPPSH',
            'nama' => 'Gudang Tempat Penimbunan Pabean BC Soetta'
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

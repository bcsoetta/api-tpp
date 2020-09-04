<?php

use App\ReferensiDokumenPenyelesaian;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeedReferensiDokumenPenyelesaianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sources = [
            "SPPB PIB",
            "SPPB BC23",
            "REEKSPOR",
            "SPPBMCP",
            "BC 16"
        ];
        // referensi dokumen penyelesaian
        foreach ($sources as $src) {
            // iteratively create it
            ReferensiDokumenPenyelesaian::create([
                'nama' => $src
            ]);
        }
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

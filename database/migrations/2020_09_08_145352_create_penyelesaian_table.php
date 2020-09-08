<?php

use App\MigrationTraitDokumen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenyelesaianTable extends Migration
{
    use MigrationTraitDokumen;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penyelesaian', function (Blueprint $table) {
            $table->id();

            // next, no_dok,
            // tgl_dok
            $this->addDokumenColumns($table);
            // entry manifest id
            // first, jenis dokumen
            $table->unsignedBigInteger('jenis_dokumen_id');
            $table->unsignedInteger('petugas_id');

            // foreign keys
            $table->foreign('petugas_id')->references('user_id')->on('sso_user_cache');
            $table->foreign('jenis_dokumen_id')->references('id')->on('referensi_dokumen_penyelesaian');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penyelesaian');
    }
}

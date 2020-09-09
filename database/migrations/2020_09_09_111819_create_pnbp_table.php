<?php

use App\MigrationTraitDokumen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePnbpTable extends Migration
{
    use MigrationTraitDokumen;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pnbp', function (Blueprint $table) {
            $table->id();

            // dokumen trait
            $this->addDokumenColumns($table);

            // entry_manifest_id
            $table->unsignedBigInteger('entry_manifest_id');

            // tgl gate in n out stored too
            $table->date('tgl_gate_in');
            $table->date('tgl_gate_out');
            $table->unsignedSmallInteger('total_hari');

            // tarif_id
            $table->decimal('tarif_pnbp',18,4,true);

            // nilai sewa
            $table->decimal('nilai_sewa',18,4,true);

            // pejabat_id
            $table->unsignedInteger('pejabat_id');

            // nama_bidang
            $table->string('nama_bidang');
            // nama_jabatan
            $table->string('nama_jabatan');

            // kode surat
            $table->string('kode_surat');

            $table->timestamps();

            // foreign key
            $table->foreign('entry_manifest_id')->references('id')->on('entry_manifest');
            $table->foreign('pejabat_id')->references('user_id')->on('sso_user_cache');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pnbp');
    }
}

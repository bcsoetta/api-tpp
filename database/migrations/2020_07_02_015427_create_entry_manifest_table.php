<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntryManifestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entry_manifest', function (Blueprint $table) {
            $table->id();

            // nomor bc11
            $table->unsignedMediumInteger('no_bc11')->index();
            $table->date('tgl_bc11')->index();
            $table->unsignedMediumInteger('pos');
            $table->unsignedMediumInteger('subpos');
            $table->unsignedMediumInteger('subsubpos');
            $table->string('kd_flight')->index();
            $table->unsignedMediumInteger('koli');
            $table->decimal('brutto', 8, 4);

            $table->string('mawb')->index();
            $table->string('hawb')->index();

            $table->text('uraian');

            $table->string('nama_importir')->index();
            $table->text('alamat_importir');

            $table->unsignedInteger('tps_id');

            $table->timestamps();
            $table->softDeletes();

            // =============INDICES===========================================
            $table->unique(['no_bc11', 'tgl_bc11', 'mawb', 'hawb']);
            
            // =============FOREIGN KEYS======================================
            $table->foreign('tps_id','em_tps_id_tps_id')->references('id')->on('tps');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entry_manifest');
    }
}

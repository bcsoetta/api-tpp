<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePencacahanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pencacahan', function (Blueprint $table) {
            $table->id();

            // pencacahan atas apa?
            $table->unsignedBigInteger('entry_manifest_id');
            $table->enum('kondisi_barang',[
                'Baik/Baru',
                'Baik/Bekas',
                'Baik',
                'Rusak',
                'Busuk'
            ])->index();
            $table->string('peruntukan_awal',32)->index();

            $table->timestamps();

            // ===================INDEX AND FOREIGN KEYS===============================
            $table->foreign('entry_manifest_id')->references('id')->on('entry_manifest');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pencacahan');
    }
}

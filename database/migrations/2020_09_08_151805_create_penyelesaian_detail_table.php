<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenyelesaianDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penyelesaian_detail', function (Blueprint $table) {
            $table->id();
            // the id of penyelesaian
            $table->unsignedBigInteger('penyelesaian_id');
            // the id of entry manifest
            $table->unsignedBigInteger('entry_manifest_id');
            $table->timestamps();

            // foreign keys
            $table->foreign('penyelesaian_id')->references('id')->on('penyelesaian');
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
        Schema::dropIfExists('penyelesaian_detail');
    }
}

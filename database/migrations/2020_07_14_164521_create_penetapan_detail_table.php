<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenetapanDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penetapan_detail', function (Blueprint $table) {
            $table->id();

            // who is our header?
            $table->unsignedBigInteger('penetapan_id');
            // for whom?
            $table->unsignedBigInteger('entry_manifest_id');

            $table->timestamps();

            // foreign keys
            // ==========================================================
            $table->foreign('penetapan_id')->references('id')->on('penetapan');
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
        Schema::dropIfExists('penetapan_detail');
    }
}

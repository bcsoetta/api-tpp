<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBastDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bast_detail', function (Blueprint $table) {
            $table->id();

            // who is our header?
            $table->unsignedBigInteger('bast_id');
            // for whom?
            $table->unsignedBigInteger('entry_manifest_id');

            $table->timestamps();

            // foreign keys
            // ==========================================================
            $table->foreign('bast_id')->references('id')->on('bast');
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
        Schema::dropIfExists('bast_detail');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracking', function (Blueprint $table) {
            $table->id();

            // what's being tracked
            $table->morphs('trackable');
            // where's it? can be any type
            $table->morphs('lokasi');
            // who's done it? could be nobody
            $table->unsignedInteger('petugas_id')->nullable();

            $table->timestamps();

            // set foreign
            $table->foreign('petugas_id','tracking_petugas_id_sso_ucache')->references('user_id')->on('sso_user_cache');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tracking');
    }
}

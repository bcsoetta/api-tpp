<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lock', function (Blueprint $table) {
            $table->id();

            // what's being locked
            $table->morphs('lockable');
            // why? nullable
            $table->string('keterangan')->nullable();
            $table->unsignedInteger('petugas_id')->nullable();  // who did this?

            $table->timestamps();

            // =========FOREIGN KEYS===================
            $table->foreign('petugas_id')->references('user_id')->on('sso_user_cache');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lock');
    }
}

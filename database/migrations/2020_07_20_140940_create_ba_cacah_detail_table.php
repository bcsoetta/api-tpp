<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaCacahDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ba_cacah_detail', function (Blueprint $table) {
            $table->id();

            // ba_cacah_id
            $table->unsignedBigInteger('ba_cacah_id');

            // entry_manifest_id *UNIQUE*
            $table->unsignedBigInteger('entry_manifest_id')->unique();

            $table->timestamps();

            // FOREIGN KEYS
            // -------------------------------------------------------------------------------
            // must refer to a ba_cacah
            $table->foreign('ba_cacah_id')->references('id')->on('ba_cacah');
            // must refer to a bast
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
        Schema::dropIfExists('ba_cacah_detail');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaCacahDetailBastTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ba_cacah_detail_bast', function (Blueprint $table) {
            $table->id();
            // ba_cacah_id
            $table->unsignedBigInteger('ba_cacah_id');

            // bast_id
            $table->unsignedBigInteger('bast_id');

            $table->timestamps();

            // FOREIGN KEYS
            // -------------------------------------------------------------------------------
            // must refer to a ba_cacah
            $table->foreign('ba_cacah_id')->references('id')->on('ba_cacah');
            // must refer to a bast
            $table->foreign('bast_id')->references('id')->on('bast');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ba_cacah_detail_bast');
    }
}

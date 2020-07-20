<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaCacahDetailPenetapanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ba_cacah_detail_penetapan', function (Blueprint $table) {
            $table->id();

            // ba_cacah_id
            $table->unsignedBigInteger('ba_cacah_id');
            // penetapan_id
            $table->unsignedBigInteger('penetapan_id');

            $table->timestamps();

            // FOREIGN KEYS
            // -------------------------------------------------------------------------------
            // must refer to a ba_cacah
            $table->foreign('ba_cacah_id')->references('id')->on('ba_cacah');
            // must refer to a penetapan
            $table->foreign('penetapan_id')->references('id')->on('penetapan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ba_cacah_detail_penetapan');
    }
}

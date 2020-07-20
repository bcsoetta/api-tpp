<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaCacahDetailPelaksana extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ba_cacah_detail_pelaksana', function (Blueprint $table) {
            $table->id();

            // ba_cacah_id
            $table->unsignedBigInteger('ba_cacah_id');

            // pelaksana_id
            $table->unsignedInteger('pelaksana_id');

            $table->timestamps();

            // FOREIGN KEYS
            // -------------------------------------------------------------------------------
            // must refer to a ba_cacah
            $table->foreign('ba_cacah_id')->references('id')->on('ba_cacah');
            // must refer to a sso_user_cache
            $table->foreign('pelaksana_id')->references('user_id')->on('sso_user_cache');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ba_cacah_detail_pelaksana');
    }
}

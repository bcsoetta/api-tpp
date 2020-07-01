<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSsoUserCacheTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sso_user_cache', function (Blueprint $table) {
            // adjust with sso id
            $table->increments('user_id');

            $table->string('username');
            $table->string('name');
            $table->string('nip');
            $table->string('pangkat');
            $table->string('penempatan');
            $table->string('status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sso_user_cache');
    }
}

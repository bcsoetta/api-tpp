<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeStBaCacahNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ba_cacah', function (Blueprint $table) {
            // make st data nullable
            $table->string('nomor_st')->nullable()->default(null)->change();
            $table->date('tgl_st')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ba_cacah', function (Blueprint $table) {
            //
        });
    }
}

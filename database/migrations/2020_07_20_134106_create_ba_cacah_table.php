<?php

use App\MigrationTraitDokumen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaCacahTable extends Migration
{
    use MigrationTraitDokumen;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ba_cacah', function (Blueprint $table) {
            $table->id();

            // common columns for dokument
            $this->addDokumenColumns($table);

            // data st
            $table->string('nomor_st');
            $table->date('tgl_st');

            // pejabat yg mengetahui
            $table->unsignedInteger('pejabat_id');

            $table->timestamps();

            // FOREIGN KEYS
            // ----------------------------------------------------
            $table->foreign('pejabat_id')->references('user_id')->on('sso_user_cache');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ba_cacah');
    }
}

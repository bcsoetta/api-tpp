<?php

use App\MigrationTraitDokumen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBastTable extends Migration
{
    use MigrationTraitDokumen;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bast', function (Blueprint $table) {
            $table->id();

            // common dok header
            $this->addDokumenColumns($table);

            // ex-p2?
            $table->boolean('ex_p2')->default(false);

            // who did this?
            $table->unsignedInteger('petugas_id');

            $table->timestamps();

            // foreign keys
            // ---------------------------------------
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
        Schema::dropIfExists('bast');
    }
}

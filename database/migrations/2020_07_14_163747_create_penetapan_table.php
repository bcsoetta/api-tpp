<?php

use App\MigrationTraitDokumen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenetapanTable extends Migration
{
    use MigrationTraitDokumen;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penetapan', function (Blueprint $table) {
            $table->id();

            // nomor surat?
            $this->addDokumenColumns($table);

            // pejabat_id
            $table->unsignedInteger('pejabat_id');

            $table->timestamps();

            // foreign keys
            // =========================
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
        Schema::dropIfExists('penetapan');
    }
}

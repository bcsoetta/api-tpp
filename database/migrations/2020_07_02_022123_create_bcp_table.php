<?php

use App\MigrationTraitDokumen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBcpTable extends Migration
{
    use MigrationTraitDokumen;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bcp', function (Blueprint $table) {
            $table->id();

            // it must have number of sequence
            $this->addDokumenColumns($table);

            // refer to which entry manifest?
            $table->unsignedBigInteger('entry_manifest_id');

            // what type of bcp? BTD/BDN
            $table->enum('jenis',['BTD','BDN'])->index();

            $table->timestamps();

            // foreign key constraints
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
        Schema::dropIfExists('bcp');
    }
}

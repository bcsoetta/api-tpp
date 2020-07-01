<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSequenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sequence', function (Blueprint $table) {
            $table->bigIncrements('id');
            // intermediate data
            $table->string('kode_sequence', 32)->index();
            $table->mediumInteger('tahun')->unsigned()->index();
            $table->integer('sequence')->unsigned()->default(0);
            // timestamps
            $table->timestamps();

            // composite index
            $table->unique(['kode_sequence', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sequence');
    }
}

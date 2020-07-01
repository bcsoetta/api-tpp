<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('status_id')->unsigned();
            $table->text('keterangan')->nullable()->default(null);
            $table->nullableMorphs('linkable');
            $table->string('other_data')->nullable()->default(null);

            $table->timestamps();

            // add foreign key?
            $table->foreign('status_id', 'status_id_fk_status_id')->references('id')->on('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status_detail', function(Blueprint $table) {
            $table->dropForeign('status_id_fk_status_id');
        });
        
        Schema::dropIfExists('status_detail');
    }
}

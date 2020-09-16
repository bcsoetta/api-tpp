<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rack', function (Blueprint $table) {
            $table->id();

            // add kode only I guess
            $table->string('kode', 16)->unique();

            // visual data?
            $table->float('x')->default(0.0);
            $table->float('y')->default(0.0);
            $table->float('rot')->default(0.0);
            $table->float('w')->default(0.0);
            $table->float('h')->default(0.0);

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
        Schema::dropIfExists('rack');
    }
}

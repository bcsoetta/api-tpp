<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('log', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->enum('tipe', array('INFO','WARNING','ERROR'))->default('INFO');
			$table->text('object', 65535)->nullable();
			$table->text('message', 65535)->nullable();
			$table->morphs('loggable');
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
		Schema::drop('log');
	}

}

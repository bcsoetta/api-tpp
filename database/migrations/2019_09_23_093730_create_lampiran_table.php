<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLampiranTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lampiran', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('resumable_upload_id');
			$table->enum('jenis',['GAMBAR','DOKUMEN','LAIN-LAIN']);
			$table->string('mime_type');
			$table->string('diskfilename')->unique('diskfilename');
			$table->string('filename');
			$table->morphs('attachable');

			// also store filesize?
			$table->integer('filesize')->unsigned()->default(0);

			$table->timestamps();
			$table->softDeletes();
		});

		DB::Statement('ALTER TABLE `lampiran` ADD `blob` MEDIUMBLOB ');
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('lampiran');
	}

}

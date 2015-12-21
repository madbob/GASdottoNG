<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionsTable extends Migration
{
	public function up()
	{
		Schema::create('permissions', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

			$table->integer('user_id')->unsigned();
			$table->string('action');
			$table->morphs('target');

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->index('id');
			$table->index('user_id');
		});
	}

	public function down()
	{
		Schema::drop('permissions');
	}
}

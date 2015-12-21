<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactsTable extends Migration
{
	public function up()
	{
		Schema::create('contacts', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

			$table->morphs('referrer');
			$table->string('name');
			$table->string('phone');
			$table->string('mail');

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('contacts');
	}
}

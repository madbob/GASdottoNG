<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactsTable extends Migration
{
	public function up()
	{
		Schema::create('contacts', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->timestamps();

			$table->morphs('referrer');
			$table->string('name');
			$table->string('phone');
			$table->string('email');

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('contacts');
	}
}

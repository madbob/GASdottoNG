<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
	public function up()
	{
		Schema::create('categories', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->timestamps();
			$table->string('name');
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('categories');
	}
}

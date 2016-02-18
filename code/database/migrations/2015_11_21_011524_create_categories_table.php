<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
	public function up()
	{
		Schema::create('categories', function (Blueprint $table) {
			$table->string('id',20)->primary();
			$table->string('sub_category',20)->primary(); /* parliamo di cone gestire il doppio livello */
			$table->timestamps();
			$table->text('description');

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('categories');
	}
}

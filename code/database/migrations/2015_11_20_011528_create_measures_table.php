<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeasuresTable extends Migration
{
	public function up()
	{
		Schema::create('measures', function (Blueprint $table) {
			$table->string('id', 100)->primary();
			$table->timestamps();
			$table->text('name', 100);
			$table->boolean('discrete_quantity');
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('measures');
	}
}

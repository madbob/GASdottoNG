<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeasuresTable extends Migration
{
	public function up()
	{
		Schema::create('measures', function (Blueprint $table) {
			$table->string('id',20)->primary();
			$table->timestamps();
			$table->text('description');
			$table->boolean('discrete_quantity'); /* allow decimal quantities and variable price */
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('measures');
	}
}

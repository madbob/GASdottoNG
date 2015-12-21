<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeasuresTable extends Migration
{
	public function up()
	{
		Schema::create('measures', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

			$table->string('name');
			$table->string('symbol');
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('measures');
	}
}

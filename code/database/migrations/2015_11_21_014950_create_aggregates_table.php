<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAggregatesTable extends Migration
{
	public function up()
	{
		Schema::create('aggregates', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('aggregates');
	}
}

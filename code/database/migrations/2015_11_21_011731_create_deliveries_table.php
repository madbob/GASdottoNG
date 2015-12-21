<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveriesTable extends Migration
{
	public function up()
	{
		Schema::create('deliveries', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

			$table->string('name');
			$table->string('address');
			$table->boolean('default');

			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('deliveries');
	}
}

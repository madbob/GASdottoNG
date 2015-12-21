<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVariantsTable extends Migration
{
	public function up()
	{
		Schema::create('variants', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

			$table->integer('product_id')->unsigned();
			$table->string('name');

			$table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('variants');
	}
}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookedProductVariantsTable extends Migration
{
	public function up()
	{
		Schema::create('booked_product_variants', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

			$table->string('product_id');
			$table->boolean('delivered');

			$table->foreign('product_id')->references('id')->on('booked_products')->onDelete('cascade');
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('booked_product_variants');
	}
}

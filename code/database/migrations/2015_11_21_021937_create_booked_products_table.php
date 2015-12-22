<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookedProductsTable extends Migration
{
	public function up()
	{
		Schema::create('booked_products', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->timestamps();

			$table->string('booking_id');
			$table->string('product_id');
			$table->decimal('quantity', 4, 2);
			$table->decimal('delivered', 4, 2);

			$table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
			$table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('booked_products');
	}
}

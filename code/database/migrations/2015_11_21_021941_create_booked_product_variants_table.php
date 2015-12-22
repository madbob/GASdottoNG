<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookedProductVariantsTable extends Migration
{
	public function up()
	{
		Schema::create('booked_product_variants', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->timestamps();

			$table->string('product_id');
			$table->string('variant_id');
			$table->string('value_id');
			$table->decimal('quantity', 4, 2);
			$table->decimal('delivered', 4, 2);

			$table->foreign('product_id')->references('id')->on('booked_products')->onDelete('cascade');
			$table->foreign('variant_id')->references('id')->on('variants')->onDelete('cascade');
			$table->foreign('value_id')->references('id')->on('variant_values')->onDelete('cascade');
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('booked_product_variants');
	}
}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductPricesTable extends Migration
{
	public function up()
	{
		Schema::create('product_prices', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->timestamps();

			$table->string('product_id');
			$table->integer('quantity');
			$table->decimal('price', 5, 2);
			$table->decimal('transport', 5, 2);

			$table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
			$table->index('id');
		});
	}

	public function down()
	{
		Schema::drop('product_prices');
	}
}

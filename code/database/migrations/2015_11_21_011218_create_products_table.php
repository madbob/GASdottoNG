<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
	public function up()
	{
		Schema::create('products', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->timestamps();

			$table->string('previous_id');

			$table->string('supplier_id');
			$table->string('name', 20);
			$table->string('supplier_code', 45)->nullable();
			$table->string('barcode', 12)->nullable();
			$table->string('category_id');
			$table->string('measure_id');
			$table->boolean('active');
			$table->text('description');
			$table->string('picture', 100)->nullable();

			$table->decimal('price', 5, 2);
			$table->decimal('transport', 5, 2);

			$table->boolean('variable');
			$table->decimal('portion_quantity', 4, 3);
			$table->integer('package_size')->unsigned();
			$table->integer('multiple')->unsigned();
			$table->decimal('min_quantity', 4, 3);
			$table->decimal('max_quantity', 4, 3);
			$table->decimal('max_available', 4, 3);

			$table->foreign('category_id')->references('id')->on('categories');
			$table->foreign('measure_id')->references('id')->on('measures');
			$table->foreign('supplier_id')->references('id')->on('suppliers');

			$table->index('id');
			$table->index('previous_id');
		});
	}

	public function down()
	{
		Schema::drop('products');
	}
}

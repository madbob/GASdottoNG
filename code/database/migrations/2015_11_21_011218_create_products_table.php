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
			$table->string('name');
			$table->string('code');
			$table->string('category_id');
			$table->string('measure_id');
			$table->boolean('active');
			$table->text('description');

			$table->decimal('price', 5, 2);
			$table->decimal('transport', 5, 2);

			$table->boolean('variable');
			$table->decimal('partitioning', 4, 2);
			$table->decimal('package', 4, 2);
			$table->decimal('minimum', 4, 2);
			$table->decimal('maximum', 4, 2);
			$table->decimal('multiple', 4, 2);

			$table->index('id');
			$table->index('previous_id');
		});
	}

	public function down()
	{
		Schema::drop('products');
	}
}

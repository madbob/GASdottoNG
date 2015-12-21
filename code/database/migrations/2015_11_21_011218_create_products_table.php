<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
	public function up()
	{
		Schema::create('products', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

			$table->integer('previous_id')->unsigned();

			$table->integer('supplier_id')->unsigned();
			$table->string('name');
			$table->string('code');
			$table->integer('category_id')->unsigned();
			$table->integer('measure_id')->unsigned();
			$table->boolean('active');
			$table->text('description');

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

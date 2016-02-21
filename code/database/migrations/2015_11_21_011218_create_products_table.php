<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
	public function up()
	{
		Schema::create('products', function (Blueprint $table) {
			$table->string('id',20);
			$table->timestamps();

		/*	$table->string('previous_id'); */

			$table->foreign('supplier_id')->references('id')->on('suppliers');
			$table->string('name',100);
			$table->string('supplier_code',45)->nullable();
			$table->string('bar_code',12)->nullable();
			$table->foreign('category_id')->references('id')->on('categories');
			$table->boolean('active');
			$table->text('description');
			$table->string('picture',100)->nullable();

			$table->foreign('measure_id')->references('id')->on('measures');
			$table->decimal('price', 5, 2);
			$table->decimal('transport', 5, 2);

			$table->boolean('variable'); /* can be 'true' only if discrete_quantity.measures is 'false' */
			$table->decimal('portion_qty', 4, 3); /* available only if discrete_quantity.measures is 'false' */
			$table->integer('package_size')->unsigned();
			$table->integer('multiple')->unsigned(); /* available only if discrete_quantity.measures is 'true' */
			$table->decimal('min_qty', 4, 3);
			$table->decimal('max_qty', 4, 3);
			$table->decimal('max_available', 4, 3);

			$table->primary(['supplier_id','id');
		/*	$table->index('previous_id'); */
		});
	}

	public function down()
	{
		Schema::drop('products');
	}
}

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

			$table->boolean('variable');
			$table->decimal('partitioning', 4, 2);
			$table->decimal('package', 4, 2);
			$table->decimal('minimum', 4, 2);
			$table->decimal('multiple', 4, 2);
			$table->decimal('totalmax', 4, 2);

			$table->primary(['supplier_id','id');
		/*	$table->index('previous_id'); */
		});
	}

	public function down()
	{
		Schema::drop('products');
	}
}

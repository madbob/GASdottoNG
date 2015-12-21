<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
	public function up()
	{
		Schema::create('orders', function (Blueprint $table) {
			$table->increments('id');
			$table->timestamps();

			$table->integer('supplier_id')->unsigned();
			$table->integer('aggregate_id')->unsigned();
			$table->date('start');
			$table->date('end');
			$table->date('shipping');
			$table->enum('status', ['open', 'closed', 'suspended', 'shipped']);

			$table->integer('payment_id')->unsigned();

			$table->index('id');
		});

		Schema::create('order_product', function (Blueprint $table) {
			$table->integer('order_id')->unsigned();
			$table->integer('product_id')->unsigned();

			$table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
			$table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
			$table->primary(['order_id', 'product_id']);
		});
	}

	public function down()
	{
		Schema::drop('orders');
		Schema::drop('order_product');
	}
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();

            $table->string('supplier_id');
            $table->integer('aggregate_id')->unsigned();
            $table->integer('aggregate_sorting')->default(0);
            $table->string('comment')->nullable();
            $table->date('start')->useCurrent();
            $table->date('end')->useCurrent();
            $table->date('shipping')->nullable();
            $table->enum('status', ['suspended', 'open', 'closed', 'shipped', 'archived']);
            $table->string('discount')->nullable();
            $table->string('transport')->nullable();
            $table->integer('payment_id')->nullable();
            $table->date('first_notify')->nullable();
            $table->date('last_notify')->nullable();

            $table->foreign('aggregate_id')->references('id')->on('aggregates')->onDelete('cascade');
        });

        Schema::create('order_product', function (Blueprint $table) {
            $table->string('order_id');
            $table->string('product_id');
            $table->boolean('discount_enabled')->default(true);
            $table->string('notes', 500)->default('');

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->primary(['order_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::drop('order_product');
        Schema::drop('orders');
    }
}

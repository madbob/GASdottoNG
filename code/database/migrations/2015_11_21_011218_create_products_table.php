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

            $table->string('supplier_id');
            $table->string('name');
            $table->string('supplier_code')->nullable();
            $table->string('barcode')->nullable();
            $table->string('category_id');
            $table->string('measure_id');
            $table->integer('vat_rate_id')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('archived')->default(false);
            $table->text('description')->nullable();
            $table->string('picture')->nullable();

            $table->decimal('price', 5, 2);
            $table->decimal('transport', 5, 2)->default(0);
            $table->string('discount')->default('');

            $table->boolean('variable')->default(false);
            $table->decimal('portion_quantity', 7, 3)->default(0);
            $table->integer('package_size')->unsigned()->default(0);
            $table->integer('multiple')->unsigned()->default(1);
            $table->decimal('min_quantity', 7, 3)->default(0);
            $table->decimal('max_quantity', 7, 3)->default(0);
            $table->decimal('max_available', 7, 3)->default(0);

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('measure_id')->references('id')->on('measures');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
        });
    }

    public function down()
    {
        Schema::drop('products');
    }
}

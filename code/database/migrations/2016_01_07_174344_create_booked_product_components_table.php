<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBookedProductComponentsTable extends Migration
{
    public function up()
    {
        Schema::create('booked_product_components', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('productvariant_id')->unsigned();
            $table->string('variant_id');
            $table->string('value_id');

            $table->foreign('productvariant_id')->references('id')->on('booked_product_variants')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('variants')->onDelete('cascade');
            $table->foreign('value_id')->references('id')->on('variant_values')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::drop('booked_product_components');
    }
}

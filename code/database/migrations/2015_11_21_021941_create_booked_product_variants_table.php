<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBookedProductVariantsTable extends Migration
{
    public function up()
    {
        Schema::create('booked_product_variants', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('product_id');
            $table->decimal('quantity', 6, 2);
            $table->decimal('delivered', 6, 3);
            $table->decimal('final_price', 6, 2)->default(0);

            $table->foreign('product_id')->references('id')->on('booked_products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::drop('booked_product_variants');
    }
}

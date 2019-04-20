<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BookedProductsDiscount extends Migration
{
    public function up()
    {
        Schema::table('booked_products', function (Blueprint $table) {
            $table->decimal('final_discount', 6, 3)->default(0);
        });
    }

    public function down()
    {
        Schema::table('booked_products', function (Blueprint $table) {
            $table->dropColumn('final_discount');
        });
    }
}

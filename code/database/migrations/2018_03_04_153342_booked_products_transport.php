<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BookedProductsTransport extends Migration
{
    public function up()
    {
        Schema::table('booked_products', function (Blueprint $table) {
            $table->decimal('final_transport', 6, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('booked_products', function (Blueprint $table) {
            $table->dropColumn('final_transport');
        });
    }
}

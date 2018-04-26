<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PaypalBalance extends Migration
{
    public function up()
    {
        Schema::table('balances', function (Blueprint $table) {
            $table->decimal('paypal', 7, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('balances', function (Blueprint $table) {
            $table->dropColumn('paypal');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UserPaymentMethod extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('payment_method_id')->default('none');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('payment_method_id');
        });
    }
}

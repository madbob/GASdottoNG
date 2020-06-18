<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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

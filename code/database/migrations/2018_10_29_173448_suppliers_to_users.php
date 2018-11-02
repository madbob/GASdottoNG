<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SuppliersToUsers extends Migration
{
    public function up()
    {
        Schema::create('supplier_user', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('user_id');
            $table->string('supplier_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->date('first_notify')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_user');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('first_notify');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ContactsForOrders extends Migration
{
    public function up()
    {
        Schema::create('order_user', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('order_id');
            $table->string('user_id');

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::drop('order_user');
    }
}

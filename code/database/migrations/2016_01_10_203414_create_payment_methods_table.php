<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentMethodsTable extends Migration
{
        public function up()
        {
                Schema::create('payment_methods', function (Blueprint $table) {
                        $table->string('id')->primary();
                        $table->timestamps();

                        $table->string('name')->unique();
                        $table->boolean('default');
                        $table->boolean('has_identifier');

                        $table->index('id');
                });
        }

        public function down()
        {
                Schema::drop('payment_methods');
        }
}

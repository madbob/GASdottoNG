<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuppliersTable extends Migration
{
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
            $table->softDeletes();

            $table->string('name');
            $table->string('business_name')->nullable();
            $table->string('description', 500)->nullable();
            $table->string('comment', 500)->nullable();

            $table->text('order_method');
            $table->text('payment_method');

            $table->string('taxcode')->nullable();
            $table->string('vat')->nullable();
        });
    }

    public function down()
    {
        Schema::drop('suppliers');
    }
}

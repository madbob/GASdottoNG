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

            $table->string('name')->unique();
            $table->string('description', 500)->nullable();
            $table->string('comment', 500)->nullable();

            $table->text('address');
            $table->string('phone')->nullable();
            $table->string('email');
            $table->string('fax')->nullable();
            $table->string('website')->nullable();

            $table->text('order_method');
            $table->text('payment_method');

            $table->string('taxcode')->nullable();
            $table->string('vat')->nullable();
            $table->float('balance', 10, 2)->default(0);

            $table->index('id');
        });
    }

    public function down()
    {
        Schema::drop('suppliers');
    }
}

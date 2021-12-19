<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSuppliersTable extends Migration
{
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();

            $table->softDeletes();
            $table->date('suspended_at')->nullable()->default(null);

            $table->string('name');
            $table->string('business_name')->nullable();
            $table->text('description')->nullable();
            $table->string('comment', 500)->nullable();

            $table->text('order_method');
            $table->text('payment_method');

            $table->string('taxcode')->nullable();
            $table->string('vat')->nullable();
        });

        Schema::create('supplier_user', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('user_id');
            $table->string('supplier_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });

        Schema::create('gas_supplier', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('gas_id');
            $table->string('supplier_id');

            $table->foreign('gas_id')->references('id')->on('gas')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::drop('gas_supplier');
        Schema::drop('supplier_user');
        Schema::drop('suppliers');
    }
}

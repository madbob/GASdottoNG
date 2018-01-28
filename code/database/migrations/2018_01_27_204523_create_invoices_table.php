<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('supplier_id');
            $table->string('number')->default('');
            $table->date('date');
            $table->enum('status', ['pending', 'verified', 'payed'])->default('pending');
            $table->decimal('total', 6, 2)->default(0);
            $table->decimal('total_vat', 6, 2)->default(0);
            $table->integer('payment_id')->unsigned()->default(0);

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });

        Schema::create('invoice_order', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('invoice_id')->unsigned();
            $table->string('order_id');

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_order');
        Schema::dropIfExists('invoices');
    }
}

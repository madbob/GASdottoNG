<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
            $table->string('updated_by')->default('');

            $table->string('gas_id');
            $table->string('supplier_id');
            $table->string('number')->default('');
            $table->date('date');
            $table->enum('status', ['pending', 'to_verify', 'verified', 'payed'])->default('pending');
            $table->decimal('total', 6, 2)->default(0);
            $table->decimal('total_vat', 6, 2)->default(0);
            $table->integer('payment_id')->nullable();
            $table->text('notes')->nullable();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });

        Schema::create('invoice_order', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('invoice_id');
            $table->string('order_id');

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });

        Schema::create('invoice_movement', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('invoice_id');
            $table->integer('movement_id')->unsigned();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('movement_id')->references('id')->on('movements')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_movement');
        Schema::dropIfExists('invoice_order');
        Schema::dropIfExists('invoices');
    }
}

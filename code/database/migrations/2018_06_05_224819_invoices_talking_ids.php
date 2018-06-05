<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InvoicesTalkingIds extends Migration
{
    public function up()
    {
        Schema::table('invoice_order', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
        });

        Schema::table('invoice_movement', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
        });

        DB::statement("ALTER TABLE `invoices` CHANGE `id` `id` VARCHAR(255) NOT NULL");

        Schema::table('invoice_order', function (Blueprint $table) {
            $table->string('invoice_id')->change();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });

        Schema::table('invoice_movement', function (Blueprint $table) {
            $table->string('invoice_id')->change();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    public function down()
    {
        //
    }
}

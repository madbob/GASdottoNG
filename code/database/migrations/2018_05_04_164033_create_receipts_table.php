<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceiptsTable extends Migration
{
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();

            $table->string('number');
            $table->date('date');
            $table->boolean('mailed')->default(false);
        });

        Schema::create('booking_receipt', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('receipt_id');
            $table->string('booking_id');

            $table->foreign('receipt_id')->references('id')->on('receipts')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_receipt');
        Schema::dropIfExists('receipts');
    }
}

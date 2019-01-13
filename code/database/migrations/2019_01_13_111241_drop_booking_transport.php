<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropBookingTransport extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('transport');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('transport', 6, 2)->default(0);
        });
    }
}

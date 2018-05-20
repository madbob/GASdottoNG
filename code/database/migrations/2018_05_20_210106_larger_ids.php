<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LargerIds extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE movements CHANGE sender_id sender_id VARCHAR(500)');
        DB::statement('ALTER TABLE movements CHANGE target_id target_id VARCHAR(500)');

        DB::statement('ALTER TABLE booked_product_variants CHANGE product_id product_id VARCHAR(500)');

        DB::statement('ALTER TABLE booked_products CHANGE id id VARCHAR(500)');
        DB::statement('ALTER TABLE booked_products CHANGE booking_id booking_id VARCHAR(500)');

        DB::statement('ALTER TABLE bookings CHANGE id id VARCHAR(500)');
    }

    public function down()
    {
        //
    }
}

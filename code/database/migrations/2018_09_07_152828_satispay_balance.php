<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SatispayBalance extends Migration
{
    public function up()
    {
        Schema::table('balances', function (Blueprint $table) {
            $table->decimal('satispay', 7, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('balances', function (Blueprint $table) {
            $table->dropColumn('satispay');
        });
    }
}

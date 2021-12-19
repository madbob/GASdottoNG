<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AutomaticMovements extends Migration
{
    public function up()
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->boolean('automatic')->default(false);
        });
    }

    public function down()
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropColumn('automatic');
        });
    }
}

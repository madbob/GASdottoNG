<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RelatedMovements extends Migration
{
    public function up()
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->unsignedBigInteger('related_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropColumn('related_id');
        });
    }
}

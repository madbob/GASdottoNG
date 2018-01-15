<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AggregateComment extends Migration
{
    public function up()
    {
        Schema::table('aggregates', function (Blueprint $table) {
            $table->string('comment')->nullable();
        });
    }

    public function down()
    {
        Schema::table('aggregates', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }
}

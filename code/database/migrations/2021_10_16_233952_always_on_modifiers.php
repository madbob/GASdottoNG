<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlwaysOnModifiers extends Migration
{
    public function up()
    {
        Schema::table('modifiers', function (Blueprint $table) {
            $table->boolean('always_on')->default(false);
        });
    }

    public function down()
    {
        Schema::table('modifiers', function (Blueprint $table) {
            $table->dropColumn('always_on');
        });
    }
}

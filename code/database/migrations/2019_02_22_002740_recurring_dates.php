<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecurringDates extends Migration
{
    public function up()
    {
        Schema::table('dates', function (Blueprint $table) {
            $table->text('recurring');
            $table->date('date')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('dates', function (Blueprint $table) {
            $table->dropColumn('recurring');
            $table->date('date')->change();
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NotesInProductOrder extends Migration
{
    public function up()
    {
        Schema::table('order_product', function (Blueprint $table) {
            $table->string('notes', 500)->default('');
        });
    }

    public function down()
    {
        Schema::table('order_product', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
}

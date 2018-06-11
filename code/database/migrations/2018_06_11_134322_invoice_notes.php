<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InvoiceNotes extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('notes');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
}

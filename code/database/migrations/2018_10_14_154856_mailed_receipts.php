<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MailedReceipts extends Migration
{
    public function up()
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->boolean('mailed')->default(false);
        });
    }

    public function down()
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn('mailed');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('symbol');
            $table->string('context')->default('default');
            $table->boolean('enabled')->default(true);
        });

        Schema::table('balances', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('balances', function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });

        Schema::dropIfExists('currencies');
    }
}

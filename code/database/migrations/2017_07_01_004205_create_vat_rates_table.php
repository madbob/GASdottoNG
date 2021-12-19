<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVatRatesTable extends Migration
{
    public function up()
    {
        Schema::create('vat_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('name');
            $table->float('percentage');
        });
    }

    public function down()
    {
        Schema::drop('vat_rates');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Gas;
use App\Supplier;
use App\Aggregate;
use App\Delivery;

class ExtendedRelationshipsMultigas extends Migration
{
    public function up()
    {
        Schema::create('gas_supplier', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('gas_id');
            $table->string('supplier_id');

            $table->foreign('gas_id')->references('id')->on('gas')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });

        Schema::create('aggregate_gas', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('gas_id');
            $table->integer('aggregate_id')->unsigned();

            $table->foreign('gas_id')->references('id')->on('gas')->onDelete('cascade');
            $table->foreign('aggregate_id')->references('id')->on('aggregates')->onDelete('cascade');
        });

        Schema::create('delivery_gas', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('gas_id');
            $table->string('delivery_id');

            $table->foreign('gas_id')->references('id')->on('gas')->onDelete('cascade');
            $table->foreign('delivery_id')->references('id')->on('deliveries')->onDelete('cascade');
        });

        $gas = Gas::orderBy('id')->first();
        $gas->suppliers()->sync(Supplier::withTrashed()->orderBy('id')->pluck('id'));
        $gas->aggregates()->sync(Aggregate::orderBy('id')->pluck('id'));
        $gas->deliveries()->sync(Delivery::orderBy('id')->pluck('id'));
    }

    public function down()
    {
        Schema::drop('gas_supplier');
        Schema::drop('aggregate_gas');
        Schema::drop('delivery_gas');
    }
}

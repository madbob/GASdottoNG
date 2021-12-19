<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMovementsTable extends Migration
{
    public function up()
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->date('date')->useCurrent();
            $table->date('registration_date')->useCurrent();
            $table->string('registerer_id');

            $table->string('sender_type')->nullable();
            $table->string('sender_id')->nullable();
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();

            $table->decimal('amount', 6, 2);
            $table->string('method');
            $table->string('type');
            $table->string('identifier');
            $table->text('notes');

            $table->boolean('archived')->default(false);
        });
    }

    public function down()
    {
        Schema::drop('movements');
    }
}

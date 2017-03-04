<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use DB;

class CreateMovementsTable extends Migration
{
    public function up()
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->date('date')->default(DB::raw('NOW()'));
            $table->date('registration_date')->default(DB::raw('NOW()'));
            $table->string('registerer_id');

            $table->string('sender_type');
            $table->string('sender_id');
            $table->string('target_type');
            $table->string('target_id');

            $table->decimal('amount', 5, 2);
            $table->string('method');
            $table->string('type');
            $table->string('identifier');
            $table->text('notes');

            $table->index('id');
        });
    }

    public function down()
    {
        Schema::drop('movements');
    }
}

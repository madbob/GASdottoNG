<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('creator_id');
            $table->text('content');
            $table->boolean('mailed');
            $table->date('start_date')->useCurrent();
            $table->date('end_date')->nullable();

            $table->index('id');
        });

        Schema::create('notification_user', function (Blueprint $table) {
            $table->integer('notification_id')->unsigned();
            $table->string('user_id');
            $table->boolean('done');

            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->primary(['notification_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::drop('notification_user');
        Schema::drop('notifications');
    }
}

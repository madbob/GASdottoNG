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

			$table->integer('creator_id')->unsigned();
			$table->text('content');
			$table->boolean('mailed');
			$table->date('expiry');

			$table->index('id');
		});

		Schema::create('notification_user', function (Blueprint $table) {
			$table->integer('notification_id')->unsigned();
			$table->integer('user_id')->unsigned();

			$table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->primary(['notification_id', 'user_id']);
		});
	}

	public function down()
	{
		Schema::drop('notifications');
		Schema::drop('notification_user');
	}
}

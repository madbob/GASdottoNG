<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRolesTable extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('name');
            $table->text('actions');
            $table->integer('parent_id')->unsigned()->default(0);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('user_id');
            $table->integer('role_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        Schema::create('attached_role_user', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('role_user_id')->unsigned();
            $table->string('target_id');
            $table->string('target_type');

            $table->foreign('role_user_id')->references('id')->on('role_user')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::drop('attached_role_user');
        Schema::drop('role_user');
        Schema::drop('roles');
    }
}

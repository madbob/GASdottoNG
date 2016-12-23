<?php

use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('comments', function ($table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('title')->nullable();
            $table->text('body');

            $table->integer('parent_id')->nullable();
            $table->integer('lft')->nullable();
            $table->integer('rgt')->nullable();
            $table->integer('depth')->nullable();

            $table->morphs('commentable');
            $table->string('user_id');

            $table->index('user_id');
            $table->index('commentable_id');
            $table->index('commentable_type');

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('comments');
    }
}

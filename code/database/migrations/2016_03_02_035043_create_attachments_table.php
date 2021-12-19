<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAttachmentsTable extends Migration
{
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('target_type');
            $table->string('target_id');
            $table->string('name')->default('');
            $table->string('filename')->default('');
            $table->string('url')->default('');
            $table->boolean('internal')->default(false);
        });

        Schema::create('attachments_access', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('user_id');
            $table->integer('attachment_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attachments_access');
        Schema::drop('attachments');
    }
}

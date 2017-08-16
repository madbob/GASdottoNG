<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('filename');
            $table->string('url')->default('');
            $table->boolean('internal')->default(false);
        });
    }

    public function down()
    {
        Schema::drop('attachments');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AttachmentsAccess extends Migration
{
    public function up()
    {
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
    }
}

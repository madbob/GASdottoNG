<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAttachments extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	Schema::create('filer_attachments', function(Blueprint $table)
	{
	    $table->increments('id');
	    $table->string('user_id');
	    $table->string('title')->nullable();
	    $table->string('description')->nullable();
	    $table->string('model_type')->nullable();
	    $table->string('model_id')->nullable();
	    $table->string('model_key')->nullable();
	    $table->string('attachment_type')->nullable();
	    $table->integer('attachment_id')->unsigned()->nullable();
	    $table->timestamps();
	});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	Schema::drop('filer_attachments');
    }

}

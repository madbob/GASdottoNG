<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();

            $table->softDeletes();
            $table->date('suspended_at')->nullable()->default(null);

            $table->string('gas_id');
            $table->string('parent_id')->nullable();
            $table->string('username')->unique();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('password');
            $table->boolean('enforce_password_change')->default(false);
            $table->date('birthday')->nullable();
            $table->integer('family_members')->unsigned()->nullable();
            $table->string('picture')->default('');
            $table->string('taxcode')->default('');
            $table->date('member_since')->useCurrent();
            $table->string('card_number')->default('');
            $table->datetime('last_login')->nullable();
            $table->string('preferred_delivery_id')->default('');
            $table->string('payment_method_id')->default('none');
            $table->text('rid')->nullable();

            $table->integer('fee_id')->default(0);
            $table->integer('deposit_id')->default(0);

            $table->rememberToken();

            $table->foreign('gas_id')->references('id')->on('gas');
        });
    }

    public function down()
    {
        Schema::drop('users');
    }
}

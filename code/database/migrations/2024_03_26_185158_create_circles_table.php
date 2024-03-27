<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('circles', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
            $table->string('updated_by')->default('');
            $table->string('name');
            $table->string('description')->default('');
            $table->string('group_id');
            $table->boolean('is_default')->default(false);

            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });

        Schema::create('circle_user', function (Blueprint $table) {
            $table->string('user_id');
            $table->string('circle_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('circle_id')->references('id')->on('circles')->onDelete('cascade');
        });

        Schema::create('circle_booking', function (Blueprint $table) {
            $table->string('booking_id');
            $table->string('circle_id');

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('circle_id')->references('id')->on('circles')->onDelete('cascade');
        });

        Schema::create('circle_order', function (Blueprint $table) {
            $table->string('order_id');
            $table->string('circle_id');

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('circle_id')->references('id')->on('circles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circle_user');
        Schema::dropIfExists('circle_booking');
        Schema::dropIfExists('circle_order');
        Schema::dropIfExists('circles');
    }
};

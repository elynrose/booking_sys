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
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
            $table->index(['schedule_id', 'status']);
            $table->index(['payment_status']);
            $table->index(['created_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['booking_id']);
            $table->index(['user_id']);
            $table->index(['status']);
            $table->index(['created_at']);
        });

        Schema::table('checkins', function (Blueprint $table) {
            $table->index(['booking_id']);
            $table->index(['user_id']);
            $table->index(['status']);
            $table->index(['checkin_time']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['trainer_id']);
            $table->index(['status']);
            $table->index(['start_time']);
            $table->index(['end_time']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['email']);
            $table->index(['role']);
            $table->index(['verified']);
            $table->index(['created_at']);
        });

        Schema::table('children', function (Blueprint $table) {
            $table->index(['user_id']);
        });

        Schema::table('recommendations', function (Blueprint $table) {
            $table->index(['user_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['schedule_id', 'status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['booking_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('checkins', function (Blueprint $table) {
            $table->dropIndex(['booking_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['checkin_time']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['trainer_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['start_time']);
            $table->dropIndex(['end_time']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['role']);
            $table->dropIndex(['verified']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('children', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });
    }
};

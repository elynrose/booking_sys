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
        Schema::create('schedule_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            
            // Check-in configuration
            $table->integer('max_checkins_per_day')->default(1);
            $table->boolean('requires_trainer_availability')->default(false);
            $table->boolean('allows_unlimited_checkins')->default(false);
            $table->integer('checkin_window_minutes')->default(0);
            $table->boolean('late_checkin_allowed')->default(true);
            $table->boolean('auto_checkout_enabled')->default(true);
            $table->boolean('session_tracking_enabled')->default(true);
            $table->json('checkin_conditions')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_types');
    }
}; 
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
        // Add default availability settings to trainers table
        Schema::table('trainers', function (Blueprint $table) {
            $table->boolean('is_available_by_default')->default(true)->after('is_active');
            $table->time('default_start_time')->nullable()->after('is_available_by_default');
            $table->time('default_end_time')->nullable()->after('default_start_time');
            $table->json('default_available_days')->nullable()->after('default_end_time'); // [0,1,2,3,4,5,6] for days of week
        });

        // Add a new table for trainer unavailability (when they mark themselves as unavailable)
        Schema::create('trainer_unavailabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('start_time')->nullable(); // null means all day
            $table->time('end_time')->nullable(); // null means all day
            $table->enum('reason', ['personal', 'sick', 'vacation', 'other'])->default('personal');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure unique unavailability per trainer/schedule/date
            $table->unique(['trainer_id', 'schedule_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainers', function (Blueprint $table) {
            $table->dropColumn(['is_available_by_default', 'default_start_time', 'default_end_time', 'default_available_days']);
        });

        Schema::dropIfExists('trainer_unavailabilities');
    }
}; 
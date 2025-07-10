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
        Schema::create('schedule_trainer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('trainer_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['primary', 'backup'])->default('primary');
            $table->integer('priority')->default(1); // For backup trainers, lower number = higher priority
            $table->timestamps();
            
            // Ensure unique combination of schedule and trainer
            $table->unique(['schedule_id', 'trainer_id']);
            
            // Index for performance
            $table->index(['schedule_id', 'role']);
            $table->index(['trainer_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_trainer');
    }
}; 
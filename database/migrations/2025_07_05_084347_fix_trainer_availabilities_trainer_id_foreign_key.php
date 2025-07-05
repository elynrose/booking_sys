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
        Schema::table('trainer_availabilities', function (Blueprint $table) {
            // Drop the existing foreign key constraint if it exists
            $table->dropForeign(['trainer_id']);
            
            // Add the correct foreign key constraint to users table
            $table->foreign('trainer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainer_availabilities', function (Blueprint $table) {
            // Drop the foreign key constraint to users
            $table->dropForeign(['trainer_id']);
            
            // Restore the original foreign key constraint to trainers table
            $table->foreign('trainer_id')->references('id')->on('trainers')->onDelete('cascade');
        });
    }
};

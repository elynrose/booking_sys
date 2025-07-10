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
        Schema::table('trainer_unavailabilities', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['trainer_id']);
            
            // Add the correct foreign key constraint
            $table->foreign('trainer_id')->references('id')->on('trainers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainer_unavailabilities', function (Blueprint $table) {
            // Drop the correct foreign key constraint
            $table->dropForeign(['trainer_id']);
            
            // Restore the original foreign key constraint
            $table->foreign('trainer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};

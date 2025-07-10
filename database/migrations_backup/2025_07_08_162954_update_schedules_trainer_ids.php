<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update trainer_id values to use the correct trainer IDs
        // Map user IDs to trainer IDs
        $trainerMap = [
            13 => DB::table('trainers')->where('user_id', 13)->value('id'),
            14 => DB::table('trainers')->where('user_id', 14)->value('id'),
            15 => DB::table('trainers')->where('user_id', 15)->value('id'),
            16 => DB::table('trainers')->where('user_id', 16)->value('id'),
            17 => DB::table('trainers')->where('user_id', 17)->value('id'),
        ];

        foreach ($trainerMap as $userId => $trainerId) {
            if ($trainerId) {
                DB::table('schedules')
                    ->where('trainer_id', $userId)
                    ->update(['trainer_id' => $trainerId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the mapping
        $trainerMap = [
            DB::table('trainers')->where('user_id', 13)->value('id') => 13,
            DB::table('trainers')->where('user_id', 14)->value('id') => 14,
            DB::table('trainers')->where('user_id', 15)->value('id') => 15,
            DB::table('trainers')->where('user_id', 16)->value('id') => 16,
            DB::table('trainers')->where('user_id', 17)->value('id') => 17,
        ];

        foreach ($trainerMap as $trainerId => $userId) {
            if ($trainerId) {
                DB::table('schedules')
                    ->where('trainer_id', $trainerId)
                    ->update(['trainer_id' => $userId]);
            }
        }
    }
};

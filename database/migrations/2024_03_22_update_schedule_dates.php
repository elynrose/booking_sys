<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing schedules to set start_date and end_date based on start_time
        DB::table('schedules')
            ->whereNull('start_date')
            ->orWhereNull('end_date')
            ->orderBy('id')
            ->each(function ($schedule) {
                $startTime = Carbon::parse($schedule->start_time);
                $endTime = Carbon::parse($schedule->end_time);
                
                DB::table('schedules')
                    ->where('id', $schedule->id)
                    ->update([
                        'start_date' => $startTime->format('Y-m-d'),
                        'end_date' => $endTime->format('Y-m-d'),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it's just updating data
    }
}; 
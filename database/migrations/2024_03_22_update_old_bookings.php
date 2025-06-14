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
        // Get the first child of each user who has bookings with null child_id
        $bookings = DB::table('bookings')
            ->whereNull('child_id')
            ->get();

        foreach ($bookings as $booking) {
            // Get the first child of the user
            $child = DB::table('children')
                ->where('user_id', $booking->user_id)
                ->first();

            if ($child) {
                // Update the booking with the child_id
                DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update(['child_id' => $child->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it's just updating data
    }
}; 
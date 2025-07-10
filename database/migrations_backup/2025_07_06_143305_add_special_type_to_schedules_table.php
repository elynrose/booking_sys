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
        // Since the type field is a string, we don't need to modify the column structure
        // The 'special' type will be automatically supported as it's a string field
        // We just need to ensure any existing validation or constraints allow it
        
        // Update any existing schedules that might have invalid types
        DB::table('schedules')->whereNotIn('type', ['group', 'private', 'special'])->update(['type' => 'group']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert any 'special' types back to 'group' if needed
        DB::table('schedules')->where('type', 'special')->update(['type' => 'group']);
    }
};

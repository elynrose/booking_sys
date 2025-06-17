<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First add the new columns as nullable
        Schema::table('children', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('name');
            $table->text('notes')->nullable()->after('gender');
        });

        // Update existing records with a default date
        DB::table('children')->update([
            'date_of_birth' => now()->subYears(5)->format('Y-m-d')
        ]);

        // Now make date_of_birth required
        Schema::table('children', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable(false)->change();
        });

        // Finally drop the age column
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn('age');
        });
    }

    public function down()
    {
        Schema::table('children', function (Blueprint $table) {
            $table->integer('age')->after('name');
        });

        // Calculate age from date_of_birth for existing records
        DB::table('children')->update([
            'age' => DB::raw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE())')
        ]);

        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'notes']);
        });
    }
}; 
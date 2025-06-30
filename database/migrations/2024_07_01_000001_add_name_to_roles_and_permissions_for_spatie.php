<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Add 'name' column to roles if it doesn't exist
        if (!Schema::hasColumn('roles', 'name')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('name')->nullable()->after('title');
            });
        }
        // Add 'name' column to permissions if it doesn't exist
        if (!Schema::hasColumn('permissions', 'name')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('name')->nullable()->after('title');
            });
        }
        // Copy 'title' values to 'name' for roles
        DB::statement("UPDATE roles SET name = title WHERE name IS NULL OR name = ''");
        // Copy 'title' values to 'name' for permissions
        DB::statement("UPDATE permissions SET name = title WHERE name IS NULL OR name = ''");
        // Make 'name' columns NOT NULL
        Schema::table('roles', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });
    }

    public function down()
    {
        // Remove 'name' columns if you want to roll back
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('name');
        });
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
}; 
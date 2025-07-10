<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
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
        // Make 'name' columns nullable again
        Schema::table('roles', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }
}; 
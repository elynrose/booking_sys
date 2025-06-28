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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('welcome_cover_image')->nullable()->after('navbar_brand_text_color');
            $table->string('welcome_hero_title')->nullable()->after('welcome_cover_image');
            $table->text('welcome_hero_description')->nullable()->after('welcome_hero_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['welcome_cover_image', 'welcome_hero_title', 'welcome_hero_description']);
        });
    }
};

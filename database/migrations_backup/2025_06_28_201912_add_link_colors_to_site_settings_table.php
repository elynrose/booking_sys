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
            // Topbar link colors
            $table->string('topbar_link_color')->default('#32325d')->after('top_navbar_color');
            $table->string('topbar_link_hover_color')->default('#6772e5')->after('topbar_link_color');
            
            // Sitewide link colors
            $table->string('link_color')->default('#6772e5')->after('topbar_link_hover_color');
            $table->string('link_hover_color')->default('#32325d')->after('link_color');
            
            // Sitewide font colors
            $table->string('sitewide_font_color')->default('#32325d')->after('link_hover_color');
            
            // Heading colors
            $table->string('h1_color')->default('#1a202c')->after('sitewide_font_color');
            $table->string('h2_color')->default('#1a202c')->after('h1_color');
            $table->string('h3_color')->default('#2d3748')->after('h2_color');
            $table->string('h4_color')->default('#2d3748')->after('h3_color');
            $table->string('h5_color')->default('#2d3748')->after('h4_color');
            $table->string('h6_color')->default('#2d3748')->after('h5_color');
            
            // Heading fonts
            $table->string('h1_font')->default('Inter')->after('h6_color');
            $table->string('h2_font')->default('Inter')->after('h1_font');
            $table->string('h3_font')->default('Inter')->after('h2_font');
            $table->string('h4_font')->default('Inter')->after('h3_font');
            $table->string('h5_font')->default('Inter')->after('h4_font');
            $table->string('h6_font')->default('Inter')->after('h5_font');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'topbar_link_color',
                'topbar_link_hover_color',
                'link_color',
                'link_hover_color',
                'sitewide_font_color',
                'h1_color',
                'h2_color',
                'h3_color',
                'h4_color',
                'h5_color',
                'h6_color',
                'h1_font',
                'h2_font',
                'h3_font',
                'h4_font',
                'h5_font',
                'h6_font'
            ]);
        });
    }
};

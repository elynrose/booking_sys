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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('Greenstreet');
            $table->text('site_description')->nullable();
            $table->text('logo')->nullable();
            $table->text('banner')->nullable();
            $table->text('site_favicon')->nullable();
            $table->text('og_image')->nullable();
            $table->text('welcome_cover_image')->nullable();
            
            // Colors
            $table->string('primary_color')->default('#6772e5');
            $table->string('secondary_color')->default('#32325d');
            $table->string('accent_color')->default('#f6f9fc');
            $table->string('success_color')->default('#0d9488');
            $table->string('warning_color')->default('#f59e0b');
            $table->string('danger_color')->default('#ef4444');
            $table->string('text_color')->default('#32325d');
            $table->string('text_muted_color')->default('#6b7280');
            $table->string('background_color')->default('#ffffff');
            $table->string('card_background_color')->default('#ffffff');
            $table->string('navigation_background_color')->default('#ffffff');
            $table->string('top_navbar_color')->default('#ffffff');
            $table->string('topbar_link_color')->default('#32325d');
            $table->string('topbar_link_hover_color')->default('#6772e5');
            $table->string('link_color')->default('#6772e5');
            $table->string('link_hover_color')->default('#32325d');
            $table->string('sitewide_font_color')->default('#32325d');
            $table->string('h1_color')->default('#1a202c');
            $table->string('h2_color')->default('#1a202c');
            $table->string('h3_color')->default('#2d3748');
            $table->string('h4_color')->default('#2d3748');
            $table->string('h5_color')->default('#2d3748');
            $table->string('h6_color')->default('#2d3748');
            $table->string('h1_font')->default('Inter');
            $table->string('h2_font')->default('Inter');
            $table->string('h3_font')->default('Inter');
            $table->string('h4_font')->default('Inter');
            $table->string('h5_font')->default('Inter');
            $table->string('h6_font')->default('Inter');
            $table->string('navbar_brand_text_color')->default('#32325d');
            $table->string('button_primary_color')->default('#6772e5');
            $table->string('button_secondary_color')->default('#f6f9fc');
            $table->string('heading_font')->default('Inter');
            $table->string('body_font')->default('Inter');
            $table->string('heading_color')->default('#32325d');
            $table->string('card_heading_color')->default('#32325d');
            $table->string('border_radius')->default('8px');
            $table->string('box_shadow')->default('0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)');
            $table->string('card_border_radius')->default('12px');
            $table->string('button_border_radius')->default('8px');
            
            // SEO
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            
            // Contact
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('contact_address')->nullable();
            
            // Social Media
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            
            // Footer
            $table->text('footer_text')->nullable();
            $table->text('footer_links')->nullable();
            $table->string('welcome_hero_title')->nullable();
            $table->text('welcome_hero_description')->nullable();
            
            // Stripe Configuration
            $table->string('stripe_publishable_key')->nullable();
            $table->string('stripe_secret_key')->nullable();
            $table->string('stripe_webhook_secret')->nullable();
            $table->boolean('stripe_enabled')->default(false);
            $table->string('stripe_currency')->default('usd');
            $table->text('stripe_photo')->nullable();
            
            // Zelle Configuration
            $table->string('zelle_email')->nullable();
            $table->string('zelle_name')->nullable();
            $table->text('zelle_instructions')->nullable();
            $table->boolean('zelle_enabled')->default(false);
            $table->text('zelle_photo')->nullable();
            
            // Cash App Configuration
            $table->text('cashapp_cashtag')->nullable();
            $table->text('cashapp_instructions')->nullable();
            $table->boolean('cashapp_enabled')->default(false);
            $table->text('cashapp_photo')->nullable();
            
            $table->string('timezone')->default('America/New_York');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
}; 
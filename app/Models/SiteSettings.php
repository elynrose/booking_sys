<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSettings extends Model
{
    protected $fillable = [
        'site_name', 'site_description', 'logo', 'favicon',
        'primary_color', 'secondary_color', 'accent_color', 'success_color', 
        'warning_color', 'danger_color', 'text_color', 'text_muted_color',
        'background_color', 'card_background_color', 'navigation_background_color',
        'top_navbar_color', 'topbar_link_color', 'topbar_link_hover_color',
        'link_color', 'link_hover_color', 'sitewide_font_color',
        'h1_color', 'h2_color', 'h3_color', 'h4_color', 'h5_color', 'h6_color',
        'h1_font', 'h2_font', 'h3_font', 'h4_font', 'h5_font', 'h6_font',
        'navbar_brand_text_color', 'button_primary_color', 'button_secondary_color',
        'heading_font', 'body_font', 'heading_color', 'card_heading_color',
        'border_radius', 'box_shadow', 'card_border_radius', 'button_border_radius',
        'meta_keywords', 'meta_description', 'og_image',
        'contact_email', 'contact_phone', 'contact_address',
        'facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url',
        'footer_text', 'footer_links', 'welcome_cover_image', 'welcome_hero_title', 'welcome_hero_description',
        'stripe_publishable_key', 'stripe_secret_key', 'stripe_webhook_secret', 'stripe_enabled', 'stripe_currency'
    ];

    /**
     * Get the current site settings (cached)
     */
    public static function getSettings()
    {
        return Cache::remember('site_settings', 3600, function () {
            return self::first() ?? self::createDefaultSettings();
        });
    }

    /**
     * Create default settings if none exist
     */
    public static function createDefaultSettings()
    {
        return self::create([
            'site_name' => 'Greenstreet',
            'site_description' => 'Modern gym management system',
            'primary_color' => '#6772e5',
            'secondary_color' => '#32325d',
            'accent_color' => '#f6f9fc',
            'success_color' => '#0d9488',
            'warning_color' => '#f59e0b',
            'danger_color' => '#ef4444',
            'text_color' => '#32325d',
            'text_muted_color' => '#6b7280',
            'background_color' => '#ffffff',
            'card_background_color' => '#ffffff',
            'navigation_background_color' => '#ffffff',
            'top_navbar_color' => '#ffffff',
            'topbar_link_color' => '#32325d',
            'topbar_link_hover_color' => '#6772e5',
            'link_color' => '#6772e5',
            'link_hover_color' => '#32325d',
            'sitewide_font_color' => '#32325d',
            'h1_color' => '#1a202c',
            'h2_color' => '#1a202c',
            'h3_color' => '#2d3748',
            'h4_color' => '#2d3748',
            'h5_color' => '#2d3748',
            'h6_color' => '#2d3748',
            'h1_font' => 'Inter',
            'h2_font' => 'Inter',
            'h3_font' => 'Inter',
            'h4_font' => 'Inter',
            'h5_font' => 'Inter',
            'h6_font' => 'Inter',
            'navbar_brand_text_color' => '#32325d',
            'button_primary_color' => '#6772e5',
            'button_secondary_color' => '#f6f9fc',
            'heading_font' => 'Inter',
            'body_font' => 'Inter',
            'heading_color' => '#32325d',
            'card_heading_color' => '#32325d',
            'border_radius' => '8px',
            'box_shadow' => '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
            'card_border_radius' => '12px',
            'button_border_radius' => '8px',
        ]);
    }

    /**
     * Clear settings cache
     */
    public static function clearCache()
    {
        Cache::forget('site_settings');
    }

    /**
     * Get logo URL
     */
    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    /**
     * Get favicon URL
     */
    public function getFaviconUrlAttribute()
    {
        return $this->favicon ? asset('storage/' . $this->favicon) : null;
    }

    /**
     * Get OG image URL
     */
    public function getOgImageUrlAttribute()
    {
        return $this->og_image ? asset('storage/' . $this->og_image) : null;
    }

    /**
     * Get welcome cover image URL
     */
    public function getWelcomeCoverImageUrlAttribute()
    {
        return $this->welcome_cover_image ? asset('storage/' . $this->welcome_cover_image) : null;
    }

    /**
     * Get Stripe configuration
     */
    public static function getStripeConfig()
    {
        $settings = self::getSettings();
        return [
            'enabled' => $settings->stripe_enabled,
            'publishable_key' => $settings->stripe_publishable_key,
            'secret_key' => $settings->stripe_secret_key,
            'webhook_secret' => $settings->stripe_webhook_secret,
            'currency' => $settings->stripe_currency ?? 'usd'
        ];
    }

    /**
     * Check if Stripe is enabled and configured
     */
    public static function isStripeEnabled()
    {
        $settings = self::getSettings();
        return $settings->stripe_enabled && 
               !empty($settings->stripe_publishable_key) && 
               !empty($settings->stripe_secret_key);
    }
} 
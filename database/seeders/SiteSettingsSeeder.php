<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSettings;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if settings already exist
        if (SiteSettings::count() > 0) {
            return;
        }

        SiteSettings::create([
            'site_name' => 'Greenstreet',
            'site_description' => 'Modern gym management system for fitness enthusiasts',
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
            'meta_keywords' => 'gym, fitness, workout, training, health, wellness',
            'meta_description' => 'Greenstreet - Modern gym management system for fitness enthusiasts. Book classes, track progress, and achieve your fitness goals.',
            'contact_email' => 'info@greenstreet.com',
            'contact_phone' => '+1 (555) 123-4567',
            'contact_address' => '123 Fitness Street, Health City, HC 12345',
            'facebook_url' => 'https://facebook.com/greenstreet',
            'twitter_url' => 'https://twitter.com/greenstreet',
            'instagram_url' => 'https://instagram.com/greenstreet',
            'linkedin_url' => 'https://linkedin.com/company/greenstreet',
            'footer_text' => '© 2024 Greenstreet. All rights reserved. Empowering fitness journeys.',
            'footer_links' => json_encode([
                ['title' => 'About Us', 'url' => '/about'],
                ['title' => 'Contact', 'url' => '/contact'],
                ['title' => 'Privacy Policy', 'url' => '/privacy'],
                ['title' => 'Terms of Service', 'url' => '/terms']
            ]),
            'welcome_hero_title' => 'Welcome to Greenstreet',
            'welcome_hero_description' => 'Your journey to fitness starts here. Join our community and achieve your goals.',
            'stripe_enabled' => false,
            'stripe_currency' => 'usd',
        ]);

        $this->command->info('Site settings seeded successfully!');
    }
} 
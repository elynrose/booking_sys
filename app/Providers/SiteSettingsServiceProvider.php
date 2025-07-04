<?php

namespace App\Providers;

use App\Models\SiteSettings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class SiteSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set application timezone from site settings
        try {
            $timezone = SiteSettings::getTimezone();
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        } catch (\Exception $e) {
            // Fallback to default timezone if site settings are not available
            config(['app.timezone' => 'America/New_York']);
            date_default_timezone_set('America/New_York');
        }

        // Share site settings with all views
        View::composer('*', function ($view) {
            $settings = SiteSettings::getSettings();
            $view->with('siteSettings', $settings);
            
            // Generate dynamic CSS based on site settings
            $dynamicCSS = $this->generateDynamicCSS($settings);
            $view->with('dynamicCSS', $dynamicCSS);
        });
    }

    /**
     * Generate dynamic CSS based on site settings
     */
    private function generateDynamicCSS($settings)
    {
        return "
        <style>
            :root {
                --primary-color: {$settings->primary_color};
                --secondary-color: {$settings->secondary_color};
                --accent-color: {$settings->accent_color};
                --success-color: {$settings->success_color};
                --warning-color: {$settings->warning_color};
                --danger-color: {$settings->danger_color};
                --text-color: {$settings->text_color};
                --text-muted-color: {$settings->text_muted_color};
                --background-color: {$settings->background_color};
                --card-background-color: {$settings->card_background_color};
                --navigation-background-color: {$settings->navigation_background_color};
                --top-navbar-color: {$settings->top_navbar_color};
                --topbar-link-color: {$settings->topbar_link_color};
                --topbar-link-hover-color: {$settings->topbar_link_hover_color};
                --link-color: {$settings->link_color};
                --link-hover-color: {$settings->link_hover_color};
                --sitewide-font-color: {$settings->sitewide_font_color};
                --h1-color: {$settings->h1_color};
                --h2-color: {$settings->h2_color};
                --h3-color: {$settings->h3_color};
                --h4-color: {$settings->h4_color};
                --h5-color: {$settings->h5_color};
                --h6-color: {$settings->h6_color};
                --h1-font: '{$settings->h1_font}', -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
                --h2-font: '{$settings->h2_font}', -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
                --h3-font: '{$settings->h3_font}', -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
                --h4-font: '{$settings->h4_font}', -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
                --h5-font: '{$settings->h5_font}', -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
                --h6-font: '{$settings->h6_font}', -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
                --navbar-brand-text-color: {$settings->navbar_brand_text_color};
                --button-primary-color: {$settings->button_primary_color};
                --button-secondary-color: {$settings->button_secondary_color};
                --heading-color: {$settings->heading_color};
                --card-heading-color: {$settings->card_heading_color};
                --border-radius: {$settings->border_radius};
                --box-shadow: {$settings->box_shadow};
                --card-border-radius: {$settings->card_border_radius};
                --button-border-radius: {$settings->button_border_radius};
                --heading-font: '{$settings->heading_font}', -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
                --body-font: '{$settings->body_font}', -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
            }

            /* Apply fonts */
            body {
                font-family: var(--body-font);
                color: var(--sitewide-font-color);
                background-color: var(--background-color);
            }

            /* Heading styles */
            h1 {
                font-family: var(--h1-font);
                color: var(--h1-color);
            }

            h2 {
                font-family: var(--h2-font);
                color: var(--h2-color);
            }

            h3 {
                font-family: var(--h3-font);
                color: var(--h3-color);
            }

            h4 {
                font-family: var(--h4-font);
                color: var(--h4-color);
            }

            h5 {
                font-family: var(--h5-font);
                color: var(--h5-color);
            }

            h6 {
                font-family: var(--h6-font);
                color: var(--h6-color);
            }

            /* Navigation styling */
            .navbar {
                background-color: var(--top-navbar-color) !important;
                border-bottom: 1px solid rgba(0,0,0,0.1);
            }

            .navbar-brand {
                color: var(--navbar-brand-text-color) !important;
                font-family: var(--heading-font);
                font-weight: 600;
            }

            .nav-link {
                color: var(--topbar-link-color) !important;
                font-weight: 500;
            }

            .nav-link:hover {
                color: var(--topbar-link-hover-color) !important;
            }

            /* Link styling */
            a {
                color: var(--link-color);
            }

            a:hover {
                color: var(--link-hover-color);
            }

            /* Card styling */
            .card {
                background-color: var(--card-background-color);
                border-radius: var(--card-border-radius);
                box-shadow: var(--box-shadow);
                border: 1px solid rgba(0,0,0,0.05);
            }

            .card-header {
                background-color: var(--card-background-color);
                border-bottom: 1px solid rgba(0,0,0,0.05);
            }

            .card-title {
                color: var(--card-heading-color);
                font-family: var(--heading-font);
                font-weight: 600;
            }

            /* Button styling */
            .btn-primary {
                background-color: var(--button-primary-color);
                border-color: var(--button-primary-color);
                border-radius: var(--button-border-radius);
            }

            .btn-primary:hover {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
            }

            .btn-secondary {
                background-color: var(--button-secondary-color);
                border-color: var(--button-secondary-color);
                border-radius: var(--button-border-radius);
                color: var(--text-color);
            }

            .btn-secondary:hover {
                background-color: var(--accent-color);
                border-color: var(--accent-color);
            }

            /* Form styling */
            .form-control {
                border-radius: var(--border-radius);
                border: 1px solid rgba(0,0,0,0.1);
            }

            .form-control:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.2rem rgba(103, 114, 229, 0.25);
            }

            /* Alert styling */
            .alert-success {
                background-color: var(--success-color);
                border-color: var(--success-color);
                color: white;
            }

            .alert-warning {
                background-color: var(--warning-color);
                border-color: var(--warning-color);
                color: white;
            }

            .alert-danger {
                background-color: var(--danger-color);
                border-color: var(--danger-color);
                color: white;
            }

            /* Badge styling */
            .badge-success {
                background-color: var(--success-color);
            }

            .badge-warning {
                background-color: var(--warning-color);
            }

            .badge-danger {
                background-color: var(--danger-color);
            }

            /* Table styling */
            .table th {
                color: var(--heading-color);
                font-family: var(--heading-font);
                font-weight: 600;
            }

            .table td {
                color: var(--text-color);
            }

            /* Text utilities */
            .text-muted {
                color: var(--text-muted-color) !important;
            }

            .text-primary {
                color: var(--primary-color) !important;
            }

            .text-success {
                color: var(--success-color) !important;
            }

            .text-warning {
                color: var(--warning-color) !important;
            }

            .text-danger {
                color: var(--danger-color) !important;
            }

            /* Background utilities */
            .bg-primary {
                background-color: var(--primary-color) !important;
            }

            .bg-success {
                background-color: var(--success-color) !important;
            }

            .bg-warning {
                background-color: var(--warning-color) !important;
            }

            .bg-danger {
                background-color: var(--danger-color) !important;
            }

            /* Custom components */
            .hero-section {
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                color: white;
            }

            .feature-card {
                background-color: var(--card-background-color);
                border-radius: var(--card-border-radius);
                box-shadow: var(--box-shadow);
                transition: transform 0.2s ease;
            }

            .feature-card:hover {
                transform: translateY(-2px);
            }

            .pricing-card {
                background-color: var(--card-background-color);
                border-radius: var(--card-border-radius);
                box-shadow: var(--box-shadow);
                border: 2px solid transparent;
                transition: border-color 0.2s ease;
            }

            .pricing-card:hover {
                border-color: var(--primary-color);
            }

            /* Footer styling */
            .footer {
                background-color: var(--secondary-color);
                color: white;
            }

            .footer a {
                color: rgba(255,255,255,0.8);
            }

            .footer a:hover {
                color: white;
            }
        </style>
        ";
    }
}

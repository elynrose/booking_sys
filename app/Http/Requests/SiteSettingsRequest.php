<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SiteSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png,jpg|max:1024',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'welcome_cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'success_color' => 'nullable|string|max:7',
            'warning_color' => 'nullable|string|max:7',
            'danger_color' => 'nullable|string|max:7',
            'text_color' => 'nullable|string|max:7',
            'text_muted_color' => 'nullable|string|max:7',
            'background_color' => 'nullable|string|max:7',
            'card_background_color' => 'nullable|string|max:7',
            'navigation_background_color' => 'nullable|string|max:7',
            'top_navbar_color' => 'nullable|string|max:7',
            'topbar_link_color' => 'nullable|string|max:7',
            'topbar_link_hover_color' => 'nullable|string|max:7',
            'link_color' => 'nullable|string|max:7',
            'link_hover_color' => 'nullable|string|max:7',
            'sitewide_font_color' => 'nullable|string|max:7',
            'h1_color' => 'nullable|string|max:7',
            'h2_color' => 'nullable|string|max:7',
            'h3_color' => 'nullable|string|max:7',
            'h4_color' => 'nullable|string|max:7',
            'h5_color' => 'nullable|string|max:7',
            'h6_color' => 'nullable|string|max:7',
            'h1_font' => 'nullable|string|max:100',
            'h2_font' => 'nullable|string|max:100',
            'h3_font' => 'nullable|string|max:100',
            'h4_font' => 'nullable|string|max:100',
            'h5_font' => 'nullable|string|max:100',
            'h6_font' => 'nullable|string|max:100',
            'navbar_brand_text_color' => 'nullable|string|max:7',
            'button_primary_color' => 'nullable|string|max:7',
            'button_secondary_color' => 'nullable|string|max:7',
            'heading_font' => 'nullable|string|max:100',
            'body_font' => 'nullable|string|max:100',
            'heading_color' => 'nullable|string|max:7',
            'card_heading_color' => 'nullable|string|max:7',
            'border_radius' => 'nullable|string|max:20',
            'box_shadow' => 'nullable|string|max:200',
            'card_border_radius' => 'nullable|string|max:20',
            'button_border_radius' => 'nullable|string|max:20',
            'meta_keywords' => 'nullable|string|max:500',
            'meta_description' => 'nullable|string|max:300',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_address' => 'nullable|string|max:500',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'footer_text' => 'nullable|string|max:1000',
            'footer_links' => 'nullable|string|max:1000',
            'welcome_hero_title' => 'nullable|string|max:255',
            'welcome_hero_description' => 'nullable|string|max:1000',
            'stripe_publishable_key' => 'nullable|string|max:255',
            'stripe_secret_key' => 'nullable|string|max:255',
            'stripe_webhook_secret' => 'nullable|string|max:255',
            'stripe_enabled' => 'nullable|boolean',
            'stripe_currency' => 'nullable|string|max:3',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'logo.image' => 'The logo must be an image file.',
            'logo.mimes' => 'The logo must be a file of type: jpeg, png, jpg, gif, svg.',
            'logo.max' => 'The logo may not be greater than 2MB.',
            'banner.image' => 'The banner must be an image file.',
            'banner.mimes' => 'The banner must be a file of type: jpeg, png, jpg, gif, svg.',
            'banner.max' => 'The banner may not be greater than 2MB.',
            'favicon.image' => 'The favicon must be an image file.',
            'favicon.mimes' => 'The favicon must be a file of type: ico, png, jpg.',
            'favicon.max' => 'The favicon may not be greater than 1MB.',
            'og_image.image' => 'The OG image must be an image file.',
            'og_image.mimes' => 'The OG image must be a file of type: jpeg, png, jpg, gif.',
            'og_image.max' => 'The OG image may not be greater than 2MB.',
            'welcome_cover_image.image' => 'The welcome cover image must be an image file.',
            'welcome_cover_image.mimes' => 'The welcome cover image must be a file of type: jpeg, png, jpg, gif.',
            'welcome_cover_image.max' => 'The welcome cover image may not be greater than 2MB.',
        ];
    }
} 
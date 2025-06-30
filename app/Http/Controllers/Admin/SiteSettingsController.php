<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Gate;
use Illuminate\Http\Response;

class SiteSettingsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('site_settings_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        
        $settings = SiteSettings::getSettings();
        return view('admin.site-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        abort_if(Gate::denies('site_settings_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'favicon' => 'nullable|image|mimes:ico,png,jpg|max:512',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
            'welcome_cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
            'welcome_hero_title' => 'nullable|string|max:255',
            'welcome_hero_description' => 'nullable|string',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'accent_color' => 'required|string|max:7',
            'success_color' => 'required|string|max:7',
            'warning_color' => 'required|string|max:7',
            'danger_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'text_muted_color' => 'required|string|max:7',
            'background_color' => 'required|string|max:7',
            'card_background_color' => 'required|string|max:7',
            'navigation_background_color' => 'required|string|max:7',
            'top_navbar_color' => 'required|string|max:7',
            'topbar_link_color' => 'required|string|max:7',
            'topbar_link_hover_color' => 'required|string|max:7',
            'link_color' => 'required|string|max:7',
            'link_hover_color' => 'required|string|max:7',
            'sitewide_font_color' => 'required|string|max:7',
            'h1_color' => 'required|string|max:7',
            'h2_color' => 'required|string|max:7',
            'h3_color' => 'required|string|max:7',
            'h4_color' => 'required|string|max:7',
            'h5_color' => 'required|string|max:7',
            'h6_color' => 'required|string|max:7',
            'h1_font' => 'required|string|max:100',
            'h2_font' => 'required|string|max:100',
            'h3_font' => 'required|string|max:100',
            'h4_font' => 'required|string|max:100',
            'h5_font' => 'required|string|max:100',
            'h6_font' => 'required|string|max:100',
            'navbar_brand_text_color' => 'required|string|max:7',
            'button_primary_color' => 'required|string|max:7',
            'button_secondary_color' => 'required|string|max:7',
            'heading_font' => 'required|string|max:100',
            'body_font' => 'required|string|max:100',
            'heading_color' => 'required|string|max:7',
            'card_heading_color' => 'required|string|max:7',
            'border_radius' => 'required|string|max:20',
            'box_shadow' => 'required|string',
            'card_border_radius' => 'required|string|max:20',
            'button_border_radius' => 'required|string|max:20',
            'meta_keywords' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'contact_address' => 'nullable|string',
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'footer_text' => 'nullable|string',
            'footer_links' => 'nullable|string',
            'stripe_publishable_key' => 'nullable|string|max:255',
            'stripe_secret_key' => 'nullable|string|max:255',
            'stripe_webhook_secret' => 'nullable|string|max:255',
            'stripe_enabled' => 'boolean',
            'stripe_currency' => 'nullable|string|max:3',
        ]);

        $settings = SiteSettings::getSettings();
        $data = $request->except(['logo', 'favicon', 'og_image', 'welcome_cover_image']);

        // Handle logo upload
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            try {
                if ($settings->logo) {
                    Storage::delete('public/' . $settings->logo);
                }
                $data['logo'] = $request->file('logo')->store('site', 'public');
            } catch (\Exception $e) {
                \Log::error('Logo upload error: ' . $e->getMessage());
                // Continue without updating the logo if upload fails
            }
        }

        // Handle favicon upload
        if ($request->hasFile('favicon') && $request->file('favicon')->isValid()) {
            try {
                if ($settings->favicon) {
                    Storage::delete('public/' . $settings->favicon);
                }
                $data['favicon'] = $request->file('favicon')->store('site', 'public');
            } catch (\Exception $e) {
                \Log::error('Favicon upload error: ' . $e->getMessage());
                // Continue without updating the favicon if upload fails
            }
        }

        // Handle OG image upload
        if ($request->hasFile('og_image') && $request->file('og_image')->isValid()) {
            try {
                if ($settings->og_image) {
                    Storage::delete('public/' . $settings->og_image);
                }
                $data['og_image'] = $request->file('og_image')->store('site', 'public');
            } catch (\Exception $e) {
                \Log::error('OG image upload error: ' . $e->getMessage());
                // Continue without updating the OG image if upload fails
            }
        }

        // Handle welcome cover image upload
        if ($request->hasFile('welcome_cover_image') && $request->file('welcome_cover_image')->isValid()) {
            try {
                if ($settings->welcome_cover_image) {
                    Storage::delete('public/' . $settings->welcome_cover_image);
                }
                $data['welcome_cover_image'] = $request->file('welcome_cover_image')->store('site', 'public');
            } catch (\Exception $e) {
                \Log::error('Welcome cover image upload error: ' . $e->getMessage());
                // Continue without updating the welcome cover image if upload fails
            }
        }

        $settings->update($data);
        SiteSettings::clearCache();

        return redirect()->route('admin.site-settings.index')
            ->with('success', 'Site settings updated successfully!');
    }
} 
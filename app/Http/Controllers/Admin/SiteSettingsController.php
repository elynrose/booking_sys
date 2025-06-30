<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSettings;
use App\Http\Requests\SiteSettingsRequest;
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

    public function update(SiteSettingsRequest $request)
    {
        abort_if(Gate::denies('site_settings_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        
        $settings = SiteSettings::getSettings();
        $data = $request->except(['logo', 'banner', 'favicon', 'og_image', 'welcome_cover_image']);

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

        // Handle banner upload
        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            try {
                if ($settings->banner) {
                    Storage::delete('public/' . $settings->banner);
                }
                $data['banner'] = $request->file('banner')->store('site', 'public');
            } catch (\Exception $e) {
                \Log::error('Banner upload error: ' . $e->getMessage());
                // Continue without updating the banner if upload fails
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
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog me-2"></i>
                        Site Settings
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.site-settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Basic Settings</h4>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="site_name">Site Name</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" value="{{ $settings->site_name }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="site_description">Site Description</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3">{{ $settings->site_description }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="timezone">Site Timezone</label>
                                    <select class="form-control" id="timezone" name="timezone">
                                        <option value="America/New_York" {{ (empty($settings->timezone) || $settings->timezone == 'America/New_York') ? 'selected' : '' }}>Eastern Time (ET)</option>
                                        <option value="America/Chicago" {{ (!empty($settings->timezone) && $settings->timezone == 'America/Chicago') ? 'selected' : '' }}>Central Time (CT)</option>
                                        <option value="America/Denver" {{ (!empty($settings->timezone) && $settings->timezone == 'America/Denver') ? 'selected' : '' }}>Mountain Time (MT)</option>
                                        <option value="America/Los_Angeles" {{ (!empty($settings->timezone) && $settings->timezone == 'America/Los_Angeles') ? 'selected' : '' }}>Pacific Time (PT)</option>
                                        <option value="America/Anchorage" {{ (!empty($settings->timezone) && $settings->timezone == 'America/Anchorage') ? 'selected' : '' }}>Alaska Time (AKT)</option>
                                        <option value="Pacific/Honolulu" {{ (!empty($settings->timezone) && $settings->timezone == 'Pacific/Honolulu') ? 'selected' : '' }}>Hawaii Time (HT)</option>
                                        <option value="UTC" {{ (!empty($settings->timezone) && $settings->timezone == 'UTC') ? 'selected' : '' }}>UTC</option>
                                        <option value="Europe/London" {{ (!empty($settings->timezone) && $settings->timezone == 'Europe/London') ? 'selected' : '' }}>London (GMT)</option>
                                        <option value="Europe/Paris" {{ (!empty($settings->timezone) && $settings->timezone == 'Europe/Paris') ? 'selected' : '' }}>Paris (CET)</option>
                                        <option value="Asia/Tokyo" {{ (!empty($settings->timezone) && $settings->timezone == 'Asia/Tokyo') ? 'selected' : '' }}>Tokyo (JST)</option>
                                        <option value="Australia/Sydney" {{ (!empty($settings->timezone) && $settings->timezone == 'Australia/Sydney') ? 'selected' : '' }}>Sydney (AEST)</option>
                                    </select>
                                    <small class="form-text text-muted">This timezone will be used for all date/time displays across the site.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Branding -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Branding</h4>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="logo">Logo</label>
                                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                    <small class="form-text text-muted">Max file size: 1MB. Supported formats: JPEG, PNG, JPG, GIF, SVG.</small>
                                    @if($settings->logo)
                                        <div class="mt-2">
                                            <img src="{{ $settings->logo_url }}" alt="Current Logo" style="max-height: 50px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="favicon">Favicon</label>
                                    <input type="file" class="form-control" id="favicon" name="favicon" accept="image/*">
                                    <small class="form-text text-muted">Max file size: 512KB. Supported formats: ICO, PNG, JPG.</small>
                                    @if($settings->favicon)
                                        <div class="mt-2">
                                            <img src="{{ $settings->favicon_url }}" alt="Current Favicon" style="max-height: 32px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="og_image">OG Image</label>
                                    <input type="file" class="form-control" id="og_image" name="og_image" accept="image/*">
                                    <small class="form-text text-muted">Max file size: 1MB. Supported formats: JPEG, PNG, JPG.</small>
                                    @if($settings->og_image)
                                        <div class="mt-2">
                                            <img src="{{ $settings->og_image_url }}" alt="Current OG Image" style="max-height: 50px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Welcome Page -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Welcome Page</h4>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="welcome_cover_image">Cover Image</label>
                                    <input type="file" class="form-control" id="welcome_cover_image" name="welcome_cover_image" accept="image/*">
                                    <small class="form-text text-muted">Max file size: 1MB. Supported formats: JPEG, PNG, JPG.</small>
                                    @if($settings->welcome_cover_image)
                                        <div class="mt-2">
                                            <img src="{{ $settings->welcome_cover_image_url }}" alt="Current Cover Image" style="max-width: 100%; max-height: 200px; border-radius: 8px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="welcome_hero_title">Hero Title</label>
                                    <input type="text" class="form-control" id="welcome_hero_title" name="welcome_hero_title" value="{{ $settings->welcome_hero_title }}" placeholder="Welcome to {{ $settings->site_name }}">
                                    <small class="form-text text-muted">Main heading displayed on the welcome page hero section.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="welcome_hero_description">Hero Description</label>
                                    <textarea class="form-control" id="welcome_hero_description" name="welcome_hero_description" rows="3" placeholder="Transform your life with our expert trainers and state-of-the-art facilities">{{ $settings->welcome_hero_description }}</textarea>
                                    <small class="form-text text-muted">Subtitle text displayed below the hero title.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Color Scheme -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Color Scheme</h4>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="primary_color">Primary Color</label>
                                    <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" value="{{ $settings->primary_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="secondary_color">Secondary Color</label>
                                    <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" value="{{ $settings->secondary_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="accent_color">Accent Color</label>
                                    <input type="color" class="form-control form-control-color" id="accent_color" name="accent_color" value="{{ $settings->accent_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="background_color">Background Color</label>
                                    <input type="color" class="form-control form-control-color" id="background_color" name="background_color" value="{{ $settings->background_color }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="success_color">Success Color</label>
                                    <input type="color" class="form-control form-control-color" id="success_color" name="success_color" value="{{ $settings->success_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="warning_color">Warning Color</label>
                                    <input type="color" class="form-control form-control-color" id="warning_color" name="warning_color" value="{{ $settings->warning_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="danger_color">Danger Color</label>
                                    <input type="color" class="form-control form-control-color" id="danger_color" name="danger_color" value="{{ $settings->danger_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="text_color">Text Color</label>
                                    <input type="color" class="form-control form-control-color" id="text_color" name="text_color" value="{{ $settings->text_color }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- UI Elements -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">UI Elements</h4>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="card_background_color">Card Background</label>
                                    <input type="color" class="form-control form-control-color" id="card_background_color" name="card_background_color" value="{{ $settings->card_background_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="navigation_background_color">Navigation Background</label>
                                    <input type="color" class="form-control form-control-color" id="navigation_background_color" name="navigation_background_color" value="{{ $settings->navigation_background_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="top_navbar_color">Top Navbar Color</label>
                                    <input type="color" class="form-control form-control-color" id="top_navbar_color" name="top_navbar_color" value="{{ $settings->top_navbar_color ?? '#ffffff' }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="text_muted_color">Text Muted Color</label>
                                    <input type="color" class="form-control form-control-color" id="text_muted_color" name="text_muted_color" value="{{ $settings->text_muted_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="navbar_brand_text_color">Navbar Brand Text Color</label>
                                    <input type="color" class="form-control form-control-color" id="navbar_brand_text_color" name="navbar_brand_text_color" value="{{ $settings->navbar_brand_text_color ?? '#32325d' }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="button_primary_color">Primary Button</label>
                                    <input type="color" class="form-control form-control-color" id="button_primary_color" name="button_primary_color" value="{{ $settings->button_primary_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="button_secondary_color">Secondary Button</label>
                                    <input type="color" class="form-control form-control-color" id="button_secondary_color" name="button_secondary_color" value="{{ $settings->button_secondary_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="heading_font">Heading Font</label>
                                    <select class="form-control" id="heading_font" name="heading_font" required>
                                        <option value="Inter" {{ $settings->heading_font == 'Inter' ? 'selected' : '' }}>Inter</option>
                                        <option value="Roboto" {{ $settings->heading_font == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                        <option value="Open Sans" {{ $settings->heading_font == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                        <option value="Lato" {{ $settings->heading_font == 'Lato' ? 'selected' : '' }}>Lato</option>
                                        <option value="Poppins" {{ $settings->heading_font == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="body_font">Body Font</label>
                                    <select class="form-control" id="body_font" name="body_font" required>
                                        <option value="Inter" {{ $settings->body_font == 'Inter' ? 'selected' : '' }}>Inter</option>
                                        <option value="Roboto" {{ $settings->body_font == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                        <option value="Open Sans" {{ $settings->body_font == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                        <option value="Lato" {{ $settings->body_font == 'Lato' ? 'selected' : '' }}>Lato</option>
                                        <option value="Poppins" {{ $settings->body_font == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Link Colors -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Link Colors</h4>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="topbar_link_color">Topbar Link Color</label>
                                    <input type="color" class="form-control form-control-color" id="topbar_link_color" name="topbar_link_color" value="{{ $settings->topbar_link_color ?? '#32325d' }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="topbar_link_hover_color">Topbar Link Hover</label>
                                    <input type="color" class="form-control form-control-color" id="topbar_link_hover_color" name="topbar_link_hover_color" value="{{ $settings->topbar_link_hover_color ?? '#6772e5' }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="link_color">Sitewide Link Color</label>
                                    <input type="color" class="form-control form-control-color" id="link_color" name="link_color" value="{{ $settings->link_color ?? '#6772e5' }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="link_hover_color">Link Hover Color</label>
                                    <input type="color" class="form-control form-control-color" id="link_hover_color" name="link_hover_color" value="{{ $settings->link_hover_color ?? '#32325d' }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Sitewide Fonts -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Sitewide Fonts</h4>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sitewide_font_color">Sitewide Font Color</label>
                                    <input type="color" class="form-control form-control-color" id="sitewide_font_color" name="sitewide_font_color" value="{{ $settings->sitewide_font_color ?? '#32325d' }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="heading_font">Heading Font</label>
                                    <select class="form-control" id="heading_font" name="heading_font" required>
                                        <option value="Inter" {{ $settings->heading_font == 'Inter' ? 'selected' : '' }}>Inter</option>
                                        <option value="Roboto" {{ $settings->heading_font == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                        <option value="Open Sans" {{ $settings->heading_font == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                        <option value="Lato" {{ $settings->heading_font == 'Lato' ? 'selected' : '' }}>Lato</option>
                                        <option value="Poppins" {{ $settings->heading_font == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="body_font">Body Font</label>
                                    <select class="form-control" id="body_font" name="body_font" required>
                                        <option value="Inter" {{ $settings->body_font == 'Inter' ? 'selected' : '' }}>Inter</option>
                                        <option value="Roboto" {{ $settings->body_font == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                        <option value="Open Sans" {{ $settings->body_font == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                        <option value="Lato" {{ $settings->body_font == 'Lato' ? 'selected' : '' }}>Lato</option>
                                        <option value="Poppins" {{ $settings->body_font == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Heading Customization -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Heading Customization</h4>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="h1_color">H1 Color</label>
                                    <input type="color" class="form-control form-control-color" id="h1_color" name="h1_color" value="{{ $settings->h1_color ?? '#1a202c' }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="h2_color">H2 Color</label>
                                    <input type="color" class="form-control form-control-color" id="h2_color" name="h2_color" value="{{ $settings->h2_color ?? '#1a202c' }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="h3_color">H3 Color</label>
                                    <input type="color" class="form-control form-control-color" id="h3_color" name="h3_color" value="{{ $settings->h3_color ?? '#2d3748' }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="h4_color">H4 Color</label>
                                    <input type="color" class="form-control form-control-color" id="h4_color" name="h4_color" value="{{ $settings->h4_color ?? '#2d3748' }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="h5_color">H5 Color</label>
                                    <input type="color" class="form-control form-control-color" id="h5_color" name="h5_color" value="{{ $settings->h5_color ?? '#2d3748' }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="h6_color">H6 Color</label>
                                    <input type="color" class="form-control form-control-color" id="h6_color" name="h6_color" value="{{ $settings->h6_color ?? '#2d3748' }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="h1_font">H1 Font</label>
                                    <select class="form-control" id="h1_font" name="h1_font" required>
                                        <option value="Inter" {{ ($settings->h1_font ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                                        <option value="Roboto" {{ ($settings->h1_font ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                        <option value="Open Sans" {{ ($settings->h1_font ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                        <option value="Lato" {{ ($settings->h1_font ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                                        <option value="Poppins" {{ ($settings->h1_font ?? 'Inter') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="h2_font">H2 Font</label>
                                    <select class="form-control" id="h2_font" name="h2_font" required>
                                        <option value="Inter" {{ ($settings->h2_font ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                                        <option value="Roboto" {{ ($settings->h2_font ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                        <option value="Open Sans" {{ ($settings->h2_font ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                        <option value="Lato" {{ ($settings->h2_font ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                                        <option value="Poppins" {{ ($settings->h2_font ?? 'Inter') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="h3_font">H3 Font</label>
                                    <select class="form-control" id="h3_font" name="h3_font" required>
                                        <option value="Inter" {{ ($settings->h3_font ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                                        <option value="Roboto" {{ ($settings->h3_font ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                        <option value="Open Sans" {{ ($settings->h3_font ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                        <option value="Lato" {{ ($settings->h3_font ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                                        <option value="Poppins" {{ ($settings->h3_font ?? 'Inter') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="h4_font">H4 Font</label>
                                    <select class="form-control" id="h4_font" name="h4_font" required>
                                        <option value="Inter" {{ ($settings->h4_font ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                                        <option value="Roboto" {{ ($settings->h4_font ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                        <option value="Open Sans" {{ ($settings->h4_font ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                        <option value="Lato" {{ ($settings->h4_font ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                                        <option value="Poppins" {{ ($settings->h4_font ?? 'Inter') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="h5_font">H5 Font</label>
                                    <select class="form-control" id="h5_font" name="h5_font" required>
                                        <option value="Inter" {{ ($settings->h5_font ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                                        <option value="Roboto" {{ ($settings->h5_font ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                        <option value="Open Sans" {{ ($settings->h5_font ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                        <option value="Lato" {{ ($settings->h5_font ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                                        <option value="Poppins" {{ ($settings->h5_font ?? 'Inter') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="h6_font">H6 Font</label>
                                    <select class="form-control" id="h6_font" name="h6_font" required>
                                        <option value="Inter" {{ ($settings->h6_font ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                                        <option value="Roboto" {{ ($settings->h6_font ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                        <option value="Open Sans" {{ ($settings->h6_font ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                        <option value="Lato" {{ ($settings->h6_font ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                                        <option value="Poppins" {{ ($settings->h6_font ?? 'Inter') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Typography -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Typography</h4>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="heading_color">Heading Color</label>
                                    <input type="color" class="form-control form-control-color" id="heading_color" name="heading_color" value="{{ $settings->heading_color }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="card_heading_color">Card Heading Color</label>
                                    <input type="color" class="form-control form-control-color" id="card_heading_color" name="card_heading_color" value="{{ $settings->card_heading_color }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Layout -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Layout</h4>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="border_radius">Border Radius</label>
                                    <input type="text" class="form-control" id="border_radius" name="border_radius" value="{{ $settings->border_radius }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="card_border_radius">Card Border Radius</label>
                                    <input type="text" class="form-control" id="card_border_radius" name="card_border_radius" value="{{ $settings->card_border_radius }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="button_border_radius">Button Border Radius</label>
                                    <input type="text" class="form-control" id="button_border_radius" name="button_border_radius" value="{{ $settings->button_border_radius }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="box_shadow">Box Shadow</label>
                                    <input type="text" class="form-control" id="box_shadow" name="box_shadow" value="{{ $settings->box_shadow }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- SEO -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">SEO</h4>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="meta_keywords">Meta Keywords</label>
                                    <textarea class="form-control" id="meta_keywords" name="meta_keywords" rows="3">{{ $settings->meta_keywords }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="meta_description">Meta Description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description" rows="3">{{ $settings->meta_description }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Contact -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Contact Information</h4>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="contact_email">Contact Email</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email" value="{{ $settings->contact_email }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="contact_phone">Contact Phone</label>
                                    <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="{{ $settings->contact_phone }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="contact_address">Contact Address</label>
                                    <textarea class="form-control" id="contact_address" name="contact_address" rows="3">{{ $settings->contact_address }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Social Media</h4>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="facebook_url">Facebook URL</label>
                                    <input type="url" class="form-control" id="facebook_url" name="facebook_url" value="{{ $settings->facebook_url }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="twitter_url">Twitter URL</label>
                                    <input type="url" class="form-control" id="twitter_url" name="twitter_url" value="{{ $settings->twitter_url }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="instagram_url">Instagram URL</label>
                                    <input type="url" class="form-control" id="instagram_url" name="instagram_url" value="{{ $settings->instagram_url }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="linkedin_url">LinkedIn URL</label>
                                    <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" value="{{ $settings->linkedin_url }}">
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Footer</h4>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="footer_text">Footer Text</label>
                                    <textarea class="form-control" id="footer_text" name="footer_text" rows="3">{{ $settings->footer_text }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="footer_links">Footer Links (JSON)</label>
                                    <textarea class="form-control" id="footer_links" name="footer_links" rows="3">{{ $settings->footer_links }}</textarea>
                                    <small class="form-text text-muted">Format: [{"title":"About","url":"/about"},{"title":"Contact","url":"/contact"}]</small>
                                </div>
                            </div>
                        </div>

                        <!-- Stripe Configuration -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">
                                    <i class="fab fa-stripe me-2"></i>
                                    Stripe Payment Configuration
                                </h4>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Configure your Stripe payment settings here. These keys will be used for processing payments instead of the .env file.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stripe_enabled">Enable Stripe Payments</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="stripe_enabled" name="stripe_enabled" value="1" {{ $settings->stripe_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="stripe_enabled">
                                            Enable Stripe payment processing
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stripe_currency">Currency</label>
                                    <select class="form-control" id="stripe_currency" name="stripe_currency">
                                        <option value="usd" {{ ($settings->stripe_currency ?? 'usd') == 'usd' ? 'selected' : '' }}>USD - US Dollar</option>
                                        <option value="eur" {{ ($settings->stripe_currency ?? 'usd') == 'eur' ? 'selected' : '' }}>EUR - Euro</option>
                                        <option value="gbp" {{ ($settings->stripe_currency ?? 'usd') == 'gbp' ? 'selected' : '' }}>GBP - British Pound</option>
                                        <option value="cad" {{ ($settings->stripe_currency ?? 'usd') == 'cad' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                        <option value="aud" {{ ($settings->stripe_currency ?? 'usd') == 'aud' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stripe_publishable_key">Publishable Key</label>
                                    <input type="text" class="form-control" id="stripe_publishable_key" name="stripe_publishable_key" value="{{ $settings->stripe_publishable_key }}" placeholder="pk_test_...">
                                    <small class="form-text text-muted">Your Stripe publishable key (starts with pk_test_ or pk_live_)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stripe_secret_key">Secret Key</label>
                                    <input type="password" class="form-control" id="stripe_secret_key" name="stripe_secret_key" value="{{ $settings->stripe_secret_key }}" placeholder="sk_test_...">
                                    <small class="form-text text-muted">Your Stripe secret key (starts with sk_test_ or sk_live_)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="stripe_webhook_secret">Webhook Secret</label>
                                    <input type="password" class="form-control" id="stripe_webhook_secret" name="stripe_webhook_secret" value="{{ $settings->stripe_webhook_secret }}" placeholder="whsec_...">
                                    <small class="form-text text-muted">Your Stripe webhook secret (starts with whsec_) - Optional for basic payment processing</small>
                                </div>
                            </div>
                        </div>

                        <!-- Zelle Configuration -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    Zelle Payment Configuration
                                </h4>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Configure your Zelle payment settings here. Zelle allows users to send money directly to your email address.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zelle_enabled">Enable Zelle Payments</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="zelle_enabled" name="zelle_enabled" value="1" {{ $settings->zelle_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="zelle_enabled">
                                            Enable Zelle payment processing
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zelle_email">Zelle Email Address</label>
                                    <input type="email" class="form-control" id="zelle_email" name="zelle_email" value="{{ $settings->zelle_email }}" placeholder="your-zelle-email@example.com">
                                    <small class="form-text text-muted">The email address associated with your Zelle account</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zelle_name">Zelle Name</label>
                                    <input type="text" class="form-control" id="zelle_name" name="zelle_name" value="{{ $settings->zelle_name }}" placeholder="Your Name">
                                    <small class="form-text text-muted">The name that appears when users send money via Zelle</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zelle_instructions">Zelle Instructions</label>
                                    <textarea class="form-control" id="zelle_instructions" name="zelle_instructions" rows="3" placeholder="Please include your name and booking ID in the memo field for faster processing.">{{ $settings->zelle_instructions }}</textarea>
                                    <small class="form-text text-muted">Instructions for users when making Zelle payments</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Save Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const stripeEnabledCheckbox = document.getElementById('stripe_enabled');
    
    // Debug logging for form submission
    form.addEventListener('submit', function(e) {
        console.log('Form submitted');
        console.log('Stripe enabled checkbox checked:', stripeEnabledCheckbox.checked);
        console.log('Stripe enabled checkbox value:', stripeEnabledCheckbox.value);
        
        // Ensure the checkbox value is properly set
        if (stripeEnabledCheckbox.checked) {
            stripeEnabledCheckbox.value = '1';
        } else {
            // Create a hidden input to ensure the field is sent as false
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'stripe_enabled';
            hiddenInput.value = '0';
            form.appendChild(hiddenInput);
        }
    });
    
    // Debug logging for checkbox changes
    stripeEnabledCheckbox.addEventListener('change', function(e) {
        console.log('Stripe enabled checkbox changed:', e.target.checked);
    });
});
</script>
@endpush

@endsection 
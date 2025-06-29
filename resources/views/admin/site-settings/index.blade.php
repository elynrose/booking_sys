@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="card-title">{{ __('app.site_settings.title') }}</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.site-settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Basic Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.basic_settings') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="site_name" class="form-label">{{ __('app.site_settings.site_name') }}</label>
                            <input type="text" class="form-control @error('site_name') is-invalid @enderror" 
                                   id="site_name" name="site_name" value="{{ $settings->site_name }}" required>
                            @error('site_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="site_description" class="form-label">{{ __('app.site_settings.site_description') }}</label>
                            <textarea class="form-control @error('site_description') is-invalid @enderror" 
                                      id="site_description" name="site_description" rows="3">{{ $settings->site_description }}</textarea>
                            @error('site_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branding -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.branding') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="logo" class="form-label">{{ __('app.site_settings.logo') }}</label>
                            @if($settings->logo)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($settings->logo) }}" alt="Current Logo" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                   id="logo" name="logo" accept="image/*">
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="favicon" class="form-label">{{ __('app.site_settings.favicon') }}</label>
                            @if($settings->favicon)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($settings->favicon) }}" alt="Current Favicon" class="img-thumbnail" style="max-height: 50px;">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('favicon') is-invalid @enderror" 
                                   id="favicon" name="favicon" accept="image/*">
                            @error('favicon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="og_image" class="form-label">{{ __('app.site_settings.og_image') }}</label>
                            @if($settings->og_image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($settings->og_image) }}" alt="Current OG Image" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('og_image') is-invalid @enderror" 
                                   id="og_image" name="og_image" accept="image/*">
                            @error('og_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Welcome Page -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.welcome_page') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="welcome_cover_image" class="form-label">{{ __('app.site_settings.welcome_cover_image') }}</label>
                            @if($settings->welcome_cover_image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($settings->welcome_cover_image) }}" alt="Current Cover Image" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('welcome_cover_image') is-invalid @enderror" 
                                   id="welcome_cover_image" name="welcome_cover_image" accept="image/*">
                            <small class="form-text text-muted">{{ __('app.site_settings.cover_image_help') }}</small>
                            @error('welcome_cover_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="welcome_hero_title" class="form-label">{{ __('app.site_settings.welcome_hero_title') }}</label>
                            <input type="text" class="form-control @error('welcome_hero_title') is-invalid @enderror" 
                                   id="welcome_hero_title" name="welcome_hero_title" value="{{ $settings->welcome_hero_title }}" placeholder="{{ __('app.site_settings.welcome_hero_title_placeholder') }}">
                            <small class="form-text text-muted">{{ __('app.site_settings.welcome_hero_title_help') }}</small>
                            @error('welcome_hero_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="form-group">
                    <label for="welcome_hero_description" class="form-label">{{ __('app.site_settings.welcome_hero_description') }}</label>
                    <textarea class="form-control @error('welcome_hero_description') is-invalid @enderror" 
                              id="welcome_hero_description" name="welcome_hero_description" rows="3" placeholder="{{ __('app.site_settings.welcome_hero_description_placeholder') }}">{{ $settings->welcome_hero_description }}</textarea>
                    <small class="form-text text-muted">{{ __('app.site_settings.welcome_hero_description_help') }}</small>
                    @error('welcome_hero_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Color Scheme -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.color_scheme') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="primary_color" class="form-label">{{ __('app.site_settings.primary_color') }}</label>
                            <input type="color" class="form-control @error('primary_color') is-invalid @enderror" 
                                   id="primary_color" name="primary_color" value="{{ $settings->primary_color }}" required>
                            @error('primary_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="secondary_color" class="form-label">{{ __('app.site_settings.secondary_color') }}</label>
                            <input type="color" class="form-control @error('secondary_color') is-invalid @enderror" 
                                   id="secondary_color" name="secondary_color" value="{{ $settings->secondary_color }}" required>
                            @error('secondary_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="accent_color" class="form-label">{{ __('app.site_settings.accent_color') }}</label>
                            <input type="color" class="form-control @error('accent_color') is-invalid @enderror" 
                                   id="accent_color" name="accent_color" value="{{ $settings->accent_color }}" required>
                            @error('accent_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="background_color" class="form-label">{{ __('app.site_settings.background_color') }}</label>
                            <input type="color" class="form-control @error('background_color') is-invalid @enderror" 
                                   id="background_color" name="background_color" value="{{ $settings->background_color }}" required>
                            @error('background_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="success_color" class="form-label">{{ __('app.site_settings.success_color') }}</label>
                    <input type="color" class="form-control @error('success_color') is-invalid @enderror" 
                           id="success_color" name="success_color" value="{{ $settings->success_color }}" required>
                    @error('success_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="warning_color" class="form-label">{{ __('app.site_settings.warning_color') }}</label>
                    <input type="color" class="form-control @error('warning_color') is-invalid @enderror" 
                           id="warning_color" name="warning_color" value="{{ $settings->warning_color }}" required>
                    @error('warning_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="danger_color" class="form-label">{{ __('app.site_settings.danger_color') }}</label>
                    <input type="color" class="form-control @error('danger_color') is-invalid @enderror" 
                           id="danger_color" name="danger_color" value="{{ $settings->danger_color }}" required>
                    @error('danger_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="text_color" class="form-label">{{ __('app.site_settings.text_color') }}</label>
                    <input type="color" class="form-control @error('text_color') is-invalid @enderror" 
                           id="text_color" name="text_color" value="{{ $settings->text_color }}" required>
                    @error('text_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- UI Elements -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.ui_elements') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="card_background_color" class="form-label">{{ __('app.site_settings.card_background_color') }}</label>
                            <input type="color" class="form-control @error('card_background_color') is-invalid @enderror" 
                                   id="card_background_color" name="card_background_color" value="{{ $settings->card_background_color }}" required>
                            @error('card_background_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="navigation_background_color" class="form-label">{{ __('app.site_settings.navigation_background_color') }}</label>
                            <input type="color" class="form-control @error('navigation_background_color') is-invalid @enderror" 
                                   id="navigation_background_color" name="navigation_background_color" value="{{ $settings->navigation_background_color }}" required>
                            @error('navigation_background_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="top_navbar_color" class="form-label">{{ __('app.site_settings.top_navbar_color') }}</label>
                            <input type="color" class="form-control @error('top_navbar_color') is-invalid @enderror" 
                                   id="top_navbar_color" name="top_navbar_color" value="{{ $settings->top_navbar_color ?? '#ffffff' }}" required>
                            @error('top_navbar_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="text_muted_color" class="form-label">{{ __('app.site_settings.text_muted_color') }}</label>
                            <input type="color" class="form-control @error('text_muted_color') is-invalid @enderror" 
                                   id="text_muted_color" name="text_muted_color" value="{{ $settings->text_muted_color }}" required>
                            @error('text_muted_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="navbar_brand_text_color" class="form-label">{{ __('app.site_settings.navbar_brand_text_color') }}</label>
                            <input type="color" class="form-control @error('navbar_brand_text_color') is-invalid @enderror" 
                                   id="navbar_brand_text_color" name="navbar_brand_text_color" value="{{ $settings->navbar_brand_text_color ?? '#32325d' }}" required>
                            @error('navbar_brand_text_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="button_primary_color" class="form-label">{{ __('app.site_settings.button_primary_color') }}</label>
                    <input type="color" class="form-control @error('button_primary_color') is-invalid @enderror" 
                           id="button_primary_color" name="button_primary_color" value="{{ $settings->button_primary_color }}" required>
                    @error('button_primary_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="button_secondary_color" class="form-label">{{ __('app.site_settings.button_secondary_color') }}</label>
                    <input type="color" class="form-control @error('button_secondary_color') is-invalid @enderror" 
                           id="button_secondary_color" name="button_secondary_color" value="{{ $settings->button_secondary_color }}" required>
                    @error('button_secondary_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="heading_font" class="form-label">{{ __('app.site_settings.heading_font') }}</label>
                    <select class="form-control @error('heading_font') is-invalid @enderror" 
                            id="heading_font" name="heading_font" required>
                        <option value="Inter" {{ $settings->heading_font == 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="Roboto" {{ $settings->heading_font == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                        <option value="Open Sans" {{ $settings->heading_font == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                        <option value="Lato" {{ $settings->heading_font == 'Lato' ? 'selected' : '' }}>Lato</option>
                        <option value="Poppins" {{ $settings->heading_font == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                    </select>
                    @error('heading_font')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="body_font" class="form-label">{{ __('app.site_settings.body_font') }}</label>
                    <select class="form-control @error('body_font') is-invalid @enderror" 
                            id="body_font" name="body_font" required>
                        <option value="Inter" {{ $settings->body_font == 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="Roboto" {{ $settings->body_font == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                        <option value="Open Sans" {{ $settings->body_font == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                        <option value="Lato" {{ $settings->body_font == 'Lato' ? 'selected' : '' }}>Lato</option>
                        <option value="Poppins" {{ $settings->body_font == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                    </select>
                    @error('body_font')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Link Colors -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.link_colors') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="topbar_link_color" class="form-label">{{ __('app.site_settings.topbar_link_color') }}</label>
                            <input type="color" class="form-control @error('topbar_link_color') is-invalid @enderror" 
                                   id="topbar_link_color" name="topbar_link_color" value="{{ $settings->topbar_link_color ?? '#32325d' }}" required>
                            @error('topbar_link_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="topbar_link_hover_color" class="form-label">{{ __('app.site_settings.topbar_link_hover_color') }}</label>
                            <input type="color" class="form-control @error('topbar_link_hover_color') is-invalid @enderror" 
                                   id="topbar_link_hover_color" name="topbar_link_hover_color" value="{{ $settings->topbar_link_hover_color ?? '#6772e5' }}" required>
                            @error('topbar_link_hover_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="link_color" class="form-label">{{ __('app.site_settings.link_color') }}</label>
                            <input type="color" class="form-control @error('link_color') is-invalid @enderror" 
                                   id="link_color" name="link_color" value="{{ $settings->link_color ?? '#6772e5' }}" required>
                            @error('link_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="link_hover_color" class="form-label">{{ __('app.site_settings.link_hover_color') }}</label>
                            <input type="color" class="form-control @error('link_hover_color') is-invalid @enderror" 
                                   id="link_hover_color" name="link_hover_color" value="{{ $settings->link_hover_color ?? '#32325d' }}" required>
                            @error('link_hover_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sitewide Fonts -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.sitewide_fonts') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="sitewide_font_color" class="form-label">{{ __('app.site_settings.sitewide_font_color') }}</label>
                            <input type="color" class="form-control @error('sitewide_font_color') is-invalid @enderror" 
                                   id="sitewide_font_color" name="sitewide_font_color" value="{{ $settings->sitewide_font_color ?? '#32325d' }}" required>
                            @error('sitewide_font_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="heading_font" class="form-label">{{ __('app.site_settings.heading_font') }}</label>
                            <select class="form-control @error('heading_font') is-invalid @enderror" 
                                    id="heading_font" name="heading_font" required>
                                <option value="Inter" {{ $settings->heading_font == 'Inter' ? 'selected' : '' }}>Inter</option>
                                <option value="Roboto" {{ $settings->heading_font == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                <option value="Open Sans" {{ $settings->heading_font == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                <option value="Lato" {{ $settings->heading_font == 'Lato' ? 'selected' : '' }}>Lato</option>
                                <option value="Poppins" {{ $settings->heading_font == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                            </select>
                            @error('heading_font')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="body_font" class="form-label">{{ __('app.site_settings.body_font') }}</label>
                            <select class="form-control @error('body_font') is-invalid @enderror" 
                                    id="body_font" name="body_font" required>
                                <option value="Inter" {{ $settings->body_font == 'Inter' ? 'selected' : '' }}>Inter</option>
                                <option value="Roboto" {{ $settings->body_font == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                <option value="Open Sans" {{ $settings->body_font == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                                <option value="Lato" {{ $settings->body_font == 'Lato' ? 'selected' : '' }}>Lato</option>
                                <option value="Poppins" {{ $settings->body_font == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                            </select>
                            @error('body_font')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Heading Customization -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.heading_customization') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="h1_color" class="form-label">{{ __('app.site_settings.h1_color') }}</label>
                            <input type="color" class="form-control @error('h1_color') is-invalid @enderror" 
                                   id="h1_color" name="h1_color" value="{{ $settings->h1_color ?? '#1a202c' }}" required>
                            @error('h1_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="h2_color" class="form-label">{{ __('app.site_settings.h2_color') }}</label>
                            <input type="color" class="form-control @error('h2_color') is-invalid @enderror" 
                                   id="h2_color" name="h2_color" value="{{ $settings->h2_color ?? '#1a202c' }}" required>
                            @error('h2_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="h3_color" class="form-label">{{ __('app.site_settings.h3_color') }}</label>
                            <input type="color" class="form-control @error('h3_color') is-invalid @enderror" 
                                   id="h3_color" name="h3_color" value="{{ $settings->h3_color ?? '#2d3748' }}" required>
                            @error('h3_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="h4_color" class="form-label">{{ __('app.site_settings.h4_color') }}</label>
                            <input type="color" class="form-control @error('h4_color') is-invalid @enderror" 
                                   id="h4_color" name="h4_color" value="{{ $settings->h4_color ?? '#2d3748' }}" required>
                            @error('h4_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="h5_color" class="form-label">{{ __('app.site_settings.h5_color') }}</label>
                            <input type="color" class="form-control @error('h5_color') is-invalid @enderror" 
                                   id="h5_color" name="h5_color" value="{{ $settings->h5_color ?? '#2d3748' }}" required>
                            @error('h5_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="h6_color" class="form-label">{{ __('app.site_settings.h6_color') }}</label>
                            <input type="color" class="form-control @error('h6_color') is-invalid @enderror" 
                                   id="h6_color" name="h6_color" value="{{ $settings->h6_color ?? '#2d3748' }}" required>
                            @error('h6_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="h1_font" class="form-label">{{ __('app.site_settings.h1_font') }}</label>
                    <select class="form-control @error('h1_font') is-invalid @enderror" 
                            id="h1_font" name="h1_font" required>
                        <option value="Inter" {{ ($settings->h1_font ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="Roboto" {{ ($settings->h1_font ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                        <option value="Open Sans" {{ ($settings->h1_font ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                        <option value="Lato" {{ ($settings->h1_font ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                        <option value="Poppins" {{ ($settings->h1_font ?? 'Inter') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                    </select>
                    @error('h1_font')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="h2_font" class="form-label">{{ __('app.site_settings.h2_font') }}</label>
                    <select class="form-control @error('h2_font') is-invalid @enderror" 
                            id="h2_font" name="h2_font" required>
                        <option value="Inter" {{ ($settings->h2_font ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="Roboto" {{ ($settings->h2_font ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                        <option value="Open Sans" {{ ($settings->h2_font ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                        <option value="Lato" {{ ($settings->h2_font ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                        <option value="Poppins" {{ ($settings->h2_font ?? 'Inter') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                    </select>
                    @error('h2_font')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="h3_font" class="form-label">{{ __('app.site_settings.h3_font') }}</label>
                    <select class="form-control @error('h3_font') is-invalid @enderror" 
                            id="h3_font" name="h3_font" required>
                        <option value="Inter" {{ ($settings->h3_font ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="Roboto" {{ ($settings->h3_font ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                        <option value="Open Sans" {{ ($settings->h3_font ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                        <option value="Lato" {{ ($settings->h3_font ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                        <option value="Poppins" {{ ($settings->h3_font ?? 'Inter') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                    </select>
                    @error('h3_font')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="h4_font" class="form-label">{{ __('app.site_settings.h4_font') }}</label>
                    <select class="form-control @error('h4_font') is-invalid @enderror" 
                            id="h4_font" name="h4_font" required>
                        <option value="Inter" {{ ($settings->h4_font ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="Roboto" {{ ($settings->h4_font ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                        <option value="Open Sans" {{ ($settings->h4_font ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                        <option value="Lato" {{ ($settings->h4_font ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                        <option value="Poppins" {{ ($settings->h4_font ?? 'Inter') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                    </select>
                    @error('h4_font')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="h5_font" class="form-label">{{ __('app.site_settings.h5_font') }}</label>
                    <select class="form-control @error('h5_font') is-invalid @enderror" 
                            id="h5_font" name="h5_font" required>
                        <option value="Inter" {{ ($settings->h5_font ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="Roboto" {{ ($settings->h5_font ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                        <option value="Open Sans" {{ ($settings->h5_font ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                        <option value="Lato" {{ ($settings->h5_font ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                        <option value="Poppins" {{ ($settings->h5_font ?? 'Inter') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                    </select>
                    @error('h5_font')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="h6_font" class="form-label">{{ __('app.site_settings.h6_font') }}</label>
                    <select class="form-control @error('h6_font') is-invalid @enderror" 
                            id="h6_font" name="h6_font" required>
                        <option value="Inter" {{ ($settings->h6_font ?? 'Inter') == 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="Roboto" {{ ($settings->h6_font ?? 'Inter') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                        <option value="Open Sans" {{ ($settings->h6_font ?? 'Inter') == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                        <option value="Lato" {{ ($settings->h6_font ?? 'Inter') == 'Lato' ? 'selected' : '' }}>Lato</option>
                        <option value="Poppins" {{ ($settings->h6_font ?? 'Inter') == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                    </select>
                    @error('h6_font')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Typography -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.typography') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="heading_color" class="form-label">{{ __('app.site_settings.heading_color') }}</label>
                            <input type="color" class="form-control @error('heading_color') is-invalid @enderror" 
                                   id="heading_color" name="heading_color" value="{{ $settings->heading_color }}" required>
                            @error('heading_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="card_heading_color" class="form-label">{{ __('app.site_settings.card_heading_color') }}</label>
                            <input type="color" class="form-control @error('card_heading_color') is-invalid @enderror" 
                                   id="card_heading_color" name="card_heading_color" value="{{ $settings->card_heading_color }}" required>
                            @error('card_heading_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Layout -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.layout') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="border_radius" class="form-label">{{ __('app.site_settings.border_radius') }}</label>
                            <input type="text" class="form-control @error('border_radius') is-invalid @enderror" 
                                   id="border_radius" name="border_radius" value="{{ $settings->border_radius }}" required>
                            @error('border_radius')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="card_border_radius" class="form-label">{{ __('app.site_settings.card_border_radius') }}</label>
                            <input type="text" class="form-control @error('card_border_radius') is-invalid @enderror" 
                                   id="card_border_radius" name="card_border_radius" value="{{ $settings->card_border_radius }}" required>
                            @error('card_border_radius')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="button_border_radius" class="form-label">{{ __('app.site_settings.button_border_radius') }}</label>
                            <input type="text" class="form-control @error('button_border_radius') is-invalid @enderror" 
                                   id="button_border_radius" name="button_border_radius" value="{{ $settings->button_border_radius }}" required>
                            @error('button_border_radius')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="box_shadow" class="form-label">{{ __('app.site_settings.box_shadow') }}</label>
                            <input type="text" class="form-control @error('box_shadow') is-invalid @enderror" 
                                   id="box_shadow" name="box_shadow" value="{{ $settings->box_shadow }}" required>
                            @error('box_shadow')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.seo') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label">{{ __('app.site_settings.meta_keywords') }}</label>
                            <textarea class="form-control @error('meta_keywords') is-invalid @enderror" 
                                      id="meta_keywords" name="meta_keywords" rows="3">{{ $settings->meta_keywords }}</textarea>
                            @error('meta_keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">{{ __('app.site_settings.meta_description') }}</label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                      id="meta_description" name="meta_description" rows="3">{{ $settings->meta_description }}</textarea>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.contact') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="contact_email" class="form-label">{{ __('app.site_settings.contact_email') }}</label>
                            <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                   id="contact_email" name="contact_email" value="{{ $settings->contact_email }}">
                            @error('contact_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="contact_phone" class="form-label">{{ __('app.site_settings.contact_phone') }}</label>
                            <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                   id="contact_phone" name="contact_phone" value="{{ $settings->contact_phone }}">
                            @error('contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="contact_address" class="form-label">{{ __('app.site_settings.contact_address') }}</label>
                            <textarea class="form-control @error('contact_address') is-invalid @enderror" 
                                      id="contact_address" name="contact_address" rows="3">{{ $settings->contact_address }}</textarea>
                            @error('contact_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.social_media') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="facebook_url" class="form-label">{{ __('app.site_settings.facebook_url') }}</label>
                            <input type="url" class="form-control @error('facebook_url') is-invalid @enderror" 
                                   id="facebook_url" name="facebook_url" value="{{ $settings->facebook_url }}">
                            @error('facebook_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="twitter_url" class="form-label">{{ __('app.site_settings.twitter_url') }}</label>
                            <input type="url" class="form-control @error('twitter_url') is-invalid @enderror" 
                                   id="twitter_url" name="twitter_url" value="{{ $settings->twitter_url }}">
                            @error('twitter_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="instagram_url" class="form-label">{{ __('app.site_settings.instagram_url') }}</label>
                            <input type="url" class="form-control @error('instagram_url') is-invalid @enderror" 
                                   id="instagram_url" name="instagram_url" value="{{ $settings->instagram_url }}">
                            @error('instagram_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="linkedin_url" class="form-label">{{ __('app.site_settings.linkedin_url') }}</label>
                            <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror" 
                                   id="linkedin_url" name="linkedin_url" value="{{ $settings->linkedin_url }}">
                            @error('linkedin_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">{{ __('app.site_settings.footer') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="footer_text" class="form-label">{{ __('app.site_settings.footer_text') }}</label>
                            <textarea class="form-control @error('footer_text') is-invalid @enderror" 
                                      id="footer_text" name="footer_text" rows="3">{{ $settings->footer_text }}</textarea>
                            @error('footer_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="footer_links" class="form-label">{{ __('app.site_settings.footer_links') }}</label>
                            <textarea class="form-control @error('footer_links') is-invalid @enderror" 
                                      id="footer_links" name="footer_links" rows="3">{{ $settings->footer_links }}</textarea>
                            <small class="form-text text-muted">{{ __('app.site_settings.footer_links_help') }}</small>
                            @error('footer_links')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stripe Configuration -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-3">
                    <i class="fab fa-stripe me-2"></i>
                    {{ __('app.site_settings.stripe_payment_configuration') }}
                </h4>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('app.site_settings.stripe_payment_configuration_help') }}
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="stripe_enabled" class="form-label">{{ __('app.site_settings.stripe_enabled') }}</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="stripe_enabled" name="stripe_enabled" value="1" {{ $settings->stripe_enabled ? 'checked' : '' }}>
                                <label class="form-check-label" for="stripe_enabled">
                                    {{ __('app.site_settings.enable_stripe_payment_processing') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="stripe_currency" class="form-label">{{ __('app.site_settings.stripe_currency') }}</label>
                            <select class="form-control @error('stripe_currency') is-invalid @enderror" 
                                    id="stripe_currency" name="stripe_currency">
                                <option value="usd" {{ ($settings->stripe_currency ?? 'usd') == 'usd' ? 'selected' : '' }}>USD - US Dollar</option>
                                <option value="eur" {{ ($settings->stripe_currency ?? 'usd') == 'eur' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="gbp" {{ ($settings->stripe_currency ?? 'usd') == 'gbp' ? 'selected' : '' }}>GBP - British Pound</option>
                                <option value="cad" {{ ($settings->stripe_currency ?? 'usd') == 'cad' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                <option value="aud" {{ ($settings->stripe_currency ?? 'usd') == 'aud' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                            </select>
                            @error('stripe_currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="stripe_publishable_key" class="form-label">{{ __('app.site_settings.stripe_publishable_key') }}</label>
                    <input type="text" class="form-control @error('stripe_publishable_key') is-invalid @enderror" 
                           id="stripe_publishable_key" name="stripe_publishable_key" value="{{ $settings->stripe_publishable_key }}" placeholder="pk_test_...">
                    <small class="form-text text-muted">{{ __('app.site_settings.stripe_publishable_key_help') }}</small>
                    @error('stripe_publishable_key')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="stripe_secret_key" class="form-label">{{ __('app.site_settings.stripe_secret_key') }}</label>
                    <input type="password" class="form-control @error('stripe_secret_key') is-invalid @enderror" 
                           id="stripe_secret_key" name="stripe_secret_key" value="{{ $settings->stripe_secret_key }}" placeholder="sk_test_...">
                    <small class="form-text text-muted">{{ __('app.site_settings.stripe_secret_key_help') }}</small>
                    @error('stripe_secret_key')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="stripe_webhook_secret" class="form-label">{{ __('app.site_settings.stripe_webhook_secret') }}</label>
                    <input type="password" class="form-control @error('stripe_webhook_secret') is-invalid @enderror" 
                           id="stripe_webhook_secret" name="stripe_webhook_secret" value="{{ $settings->stripe_webhook_secret }}" placeholder="whsec_...">
                    <small class="form-text text-muted">{{ __('app.site_settings.stripe_webhook_secret_help') }} - {{ __('app.site_settings.stripe_webhook_secret_optional') }}</small>
                    @error('stripe_webhook_secret')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>{{ __('app.site_settings.save_settings') }}
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>{{ __('app.actions.back') }}
                </a>
            </div>
        </div>
    </form>
</div>
@endsection 
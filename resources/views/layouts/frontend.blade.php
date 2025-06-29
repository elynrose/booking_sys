<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $siteSettings->site_name ?? config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    
    <!-- Dynamic CSS from Site Settings -->
    {!! $dynamicCSS ?? '' !!}
    
    @stack('styles')
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    @if($siteSettings->logo)
                        <img src="{{ $siteSettings->logo_url }}" alt="{{ $siteSettings->site_name }}" height="30" class="d-inline-block align-top">
                    @else
                        {{ $siteSettings->site_name ?? config('app.name', 'Laravel') }}
                    @endif
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @guest
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('frontend.home') }}">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            @if(auth()->user()->hasRole('trainer'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('frontend.trainer.index') }}">
                                    <i class="fas fa-user-tie"></i> Trainer Dashboard
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('user'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('frontend.schedules.index') }}">{{ __('app.schedules.title') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('bookings.index') }}">
                                    <i class="fas fa-book"></i> My Bookings
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('frontend.children.index') }}">{{ __('app.children.title') }}</a>
                            </li>
                            @endif
                        @endguest
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('app.navigation.login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('app.navigation.register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('frontend.profile.index') }}">{{ __('app.navigation.my_profile') }}</a>
                                    @can('user_management_access')
                                        <a class="dropdown-item" href="/admin">{{ __('app.navigation.admin') }}</a>
                                    @endcan
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('app.navigation.logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                            @if(auth()->check())
                                <!-- DEBUG: Show user roles -->
                                <li class="nav-item">
                                    <span class="nav-link text-danger">Roles: {{ implode(', ', auth()->user()->roles->pluck('title')->toArray()) }}</span>
                                </li>
                            @endif
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @if(session('message'))
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-success" role="alert">{{ session('message') }}</div>
                        </div>
                    </div>
                </div>
            @endif
            @if($errors->count() > 0)
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger">
                                <ul class="list-unstyled mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="container py-4">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="{{ asset('js/main.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
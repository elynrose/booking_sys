@extends('layouts.app')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Demo Credentials Section -->
        <div class="card mx-4 mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle mr-2"></i>
                    Demo Account Credentials
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="demo-account">
                            <h6 class="text-primary">
                                <i class="fas fa-user-shield mr-1"></i>
                                Admin Account
                            </h6>
                            <p class="mb-1"><strong>Email:</strong> admin@example.com</p>
                            <p class="mb-1"><strong>Password:</strong> password</p>
                            <button class="btn btn-sm btn-outline-primary mt-2" onclick="fillCredentials('admin@example.com', 'password')">
                                <i class="fas fa-mouse-pointer mr-1"></i>
                                Use This Account
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="demo-account">
                            <h6 class="text-success">
                                <i class="fas fa-user mr-1"></i>
                                Demo Parent Account
                            </h6>
                            <p class="mb-1"><strong>Email:</strong> oking@example.com</p>
                            <p class="mb-1"><strong>Password:</strong> password</p>
                            <button class="btn btn-sm btn-outline-success mt-2" onclick="fillCredentials('oking@example.com', 'password')">
                                <i class="fas fa-mouse-pointer mr-1"></i>
                                Use This Account
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="demo-account">
                            <h6 class="text-warning">
                                <i class="fas fa-user-tie mr-1"></i>
                                Trainer Account
                            </h6>
                            <p class="mb-1"><strong>Email:</strong> trainer@example.com</p>
                            <p class="mb-1"><strong>Password:</strong> password</p>
                            <button class="btn btn-sm btn-outline-warning mt-2" onclick="fillCredentials('trainer@example.com', 'password')">
                                <i class="fas fa-mouse-pointer mr-1"></i>
                                Use This Account
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Login Form -->
        <div class="card mx-4">
            <div class="card-body p-4">
                <h1>{{ trans('panel.site_title') }}</h1>

                <p class="text-muted">{{ trans('global.login') }}</p>

                @if(session('message'))
                    <div class="alert alert-info" role="alert">
                        {{ session('message') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-user"></i>
                            </span>
                        </div>

                        <input id="email" name="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required autocomplete="email" autofocus placeholder="{{ trans('global.login_email') }}" value="{{ old('email', null) }}">

                        @if($errors->has('email'))
                            <div class="invalid-feedback">
                                {{ $errors->first('email') }}
                            </div>
                        @endif
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        </div>

                        <input id="password" name="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required placeholder="{{ trans('global.login_password') }}">

                        @if($errors->has('password'))
                            <div class="invalid-feedback">
                                {{ $errors->first('password') }}
                            </div>
                        @endif
                    </div>

                    <div class="input-group mb-4">
                        <div class="form-check checkbox">
                            <input class="form-check-input" name="remember" type="checkbox" id="remember" style="vertical-align: middle;" />
                            <label class="form-check-label" for="remember" style="vertical-align: middle;">
                                {{ trans('global.remember_me') }}
                            </label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary px-4">
                                {{ trans('global.login') }}
                            </button>
                        </div>
                        <div class="col-6 text-right">
                            @if(Route::has('password.request'))
                                <a class="btn btn-link px-0" href="{{ route('password.request') }}">
                                    {{ trans('global.forgot_password') }}
                                </a><br>
                            @endif
                            <a class="btn btn-link px-0" href="{{ route('register') }}">
                                {{ trans('global.register') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.demo-account {
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background-color: #f8f9fa;
    height: 100%;
}

.demo-account h6 {
    margin-bottom: 15px;
    font-weight: 600;
}

.demo-account p {
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.demo-account button {
    width: 100%;
}

.card-header.bg-info {
    background-color: #17a2b8 !important;
}
</style>

<script>
function fillCredentials(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
    
    // Add a visual feedback
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password');
    
    emailField.style.backgroundColor = '#d4edda';
    passwordField.style.backgroundColor = '#d4edda';
    
    setTimeout(() => {
        emailField.style.backgroundColor = '';
        passwordField.style.backgroundColor = '';
    }, 1000);
    
    // Focus on the login button
    document.querySelector('button[type="submit"]').focus();
}
</script>
@endsection
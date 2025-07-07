@extends('layouts.app')
@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card mx-4">
            <div class="card-body p-4">

                <form method="POST" action="{{ route('register') }}" id="registration-form">
                    {{ csrf_field() }}

                    <h1>{{ trans('panel.site_title') }}</h1>
                    <p class="text-muted">{{ trans('global.register') }}</p>

                    <!-- Honeypot fields (hidden from users) -->
                    <div style="display: none;">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                        <input type="text" name="phone_number" tabindex="-1" autocomplete="off">
                        <input type="text" name="company" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-user fa-fw"></i>
                            </span>
                        </div>
                        <input type="text" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required autofocus placeholder="{{ trans('global.user_name') }}" value="{{ old('name', null) }}" minlength="2" maxlength="50">
                        @if($errors->has('name'))
                            <div class="invalid-feedback">
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-envelope fa-fw"></i>
                            </span>
                        </div>
                        <input type="email" name="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" required placeholder="{{ trans('global.login_email') }}" value="{{ old('email', null) }}" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                        @if($errors->has('email'))
                            <div class="invalid-feedback">
                                {{ $errors->first('email') }}
                            </div>
                        @endif
                    </div>

                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-lock fa-fw"></i>
                            </span>
                        </div>
                        <input type="password" name="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required placeholder="{{ trans('global.login_password') }}" minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                        @if($errors->has('password'))
                            <div class="invalid-feedback">
                                {{ $errors->first('password') }}
                            </div>
                        @endif
                    </div>

                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-lock fa-fw"></i>
                            </span>
                        </div>
                        <input type="password" name="password_confirmation" class="form-control" required placeholder="{{ trans('global.login_password_confirmation') }}" minlength="8">
                    </div>

                    <!-- CAPTCHA -->
                    <div class="mb-3">
                        <label for="captcha" class="form-label">Security Verification</label>
                        <div class="captcha-container">
                            <span class="captcha-image">{!! captcha_img() !!}</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="refreshCaptcha()">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                        </div>
                        <input type="text" name="captcha" class="form-control mt-2{{ $errors->has('captcha') ? ' is-invalid' : '' }}" required placeholder="Enter the code above">
                        @if($errors->has('captcha'))
                            <div class="invalid-feedback">
                                {{ $errors->first('captcha') }}
                            </div>
                        @endif
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input{{ $errors->has('terms') ? ' is-invalid' : '' }}" type="checkbox" name="terms" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" target="_blank">Terms and Conditions</a> and <a href="#" target="_blank">Privacy Policy</a>
                            </label>
                            @if($errors->has('terms'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('terms') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <button type="submit" class="btn btn-block btn-primary" id="submit-btn">
                        {{ trans('global.register') }}
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>

<style>
.captcha-container {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.captcha-image {
    border: 1px solid #ddd;
    padding: 5px;
    background: #f8f9fa;
}
</style>

<script>
function refreshCaptcha() {
    fetch('{{ route("captcha.refresh") }}')
        .then(response => response.json())
        .then(data => {
            document.querySelector('.captcha-image').innerHTML = data.captcha;
        });
}

// Form submission protection
document.getElementById('registration-form').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Registering...';
    
    // Add a small delay to prevent rapid submissions
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '{{ trans("global.register") }}';
    }, 3000);
});

// Prevent form resubmission
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>

@endsection
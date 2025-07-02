@extends('layouts.frontend')
@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    {{ trans('global.my_profile') }}
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('frontend.profile.index') }}">
                        @csrf
                        <div class="form-group">
                            <label class="required" for="name">{{ trans('cruds.user.fields.name') }}</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" required>
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="required" for="title">{{ trans('cruds.user.fields.email') }}</label>
                            <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" type="text" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" required>
                            @if($errors->has('email'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif
                        </div>
                        @if(auth()->user()->member_id)
                        <div class="form-group">
                            <label for="member_id">Member ID</label>
                            <input class="form-control" type="text" name="member_id" id="member_id" value="{{ auth()->user()->member_id }}" readonly>
                            <small class="form-text text-muted">Your unique member identification number</small>
                        </div>
                        @endif
                        <div class="form-group">
                            <label class="required" for="timezone">Timezone</label>
                            <select class="form-control {{ $errors->has('timezone') ? 'is-invalid' : '' }}" name="timezone" id="timezone" required>
                                @foreach(timezone_identifiers_list() as $timezone)
                                    <option value="{{ $timezone }}" {{ old('timezone', auth()->user()->timezone ?? 'UTC') == $timezone ? 'selected' : '' }}>
                                        {{ $timezone }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Select your local timezone for accurate check-in timing.</small>
                            @if($errors->has('timezone'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('timezone') }}
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <button class="btn btn-danger" type="submit">
                                {{ trans('global.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    {{ trans('global.change_password') }}
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('frontend.profile.password') }}">
                        @csrf
                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                            <label class="required" for="password">New {{ trans('cruds.user.fields.password') }}</label>
                            <input class="form-control" type="password" name="password" id="password" required>
                            @if($errors->has('password'))
                                <span class="help-block" role="alert">{{ $errors->first('password') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="required" for="password_confirmation">Repeat New {{ trans('cruds.user.fields.password') }}</label>
                            <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" required>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-danger" type="submit">
                                {{ trans('global.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    {{ trans('global.delete_account') }}
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('frontend.profile.destroy') }}" onsubmit="return prompt('{{ __('global.delete_account_warning') }}') == '{{ auth()->user()->email }}'">
                        @csrf
                        <div class="form-group">
                            <button class="btn btn-danger" type="submit">
                                {{ trans('global.delete') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if(Route::has('frontend.profile.toggle-two-factor'))
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        {{ trans('global.two_factor.title') }}
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('frontend.profile.toggle-two-factor') }}">
                            @csrf
                            <div class="form-group">
                                <button class="btn btn-danger" type="submit">
                                    {{ auth()->user()->two_factor ? trans('global.two_factor.disable') : trans('global.two_factor.enable') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timezoneSelect = document.getElementById('timezone');
    const timezoneSearch = document.getElementById('timezone-search');
    const timezoneOptions = Array.from(timezoneSelect.options);
    
    timezoneSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        timezoneOptions.forEach(option => {
            const text = option.text.toLowerCase();
            option.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});
</script>
@endpush
@endsection
@extends('layouts.frontend')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Find Your Classes</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        <i class="fas fa-id-card fa-3x text-primary mb-3"></i>
                        <h5>Find Your Classes</h5>
                        <p class="text-muted">Enter your information to find your classes for today.</p>
                    </div>

                    <form action="{{ route('frontend.checkins.verify') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="member_id" class="form-label">Enter Your Member ID</label>
                            <input type="text" class="form-control @error('member_id') is-invalid @enderror" 
                                id="member_id" name="member_id" 
                                value="{{ old('member_id', auth()->check() ? auth()->user()->member_id : '') }}" 
                                placeholder="Enter your member ID" required>
                            @if(auth()->check() && auth()->user()->member_id)
                                <div class="form-text text-success">
                                    <i class="fas fa-check-circle me-1"></i> Pre-filled with your member ID
                                </div>
                            @endif
                            @error('member_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Find My Classes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.input-group-text {
    background-color: #f8f9fa;
}
</style>
@endsection 
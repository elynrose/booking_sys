@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Check In</h2>
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('checkins.store') }}" method="POST" id="checkinForm">
                        @csrf
                        <div class="mb-4">
                            <label for="member_id" class="form-label">Member ID</label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('member_id') is-invalid @enderror" 
                                   id="member_id" 
                                   name="member_id" 
                                   value="{{ old('member_id') }}"
                                   placeholder="Enter your member ID"
                                   required>
                            @error('member_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Check In
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkinForm');
    const memberIdInput = document.getElementById('member_id');

    form.addEventListener('submit', function(e) {
        if (!memberIdInput.value.trim()) {
            e.preventDefault();
            memberIdInput.classList.add('is-invalid');
        }
    });

    memberIdInput.addEventListener('input', function() {
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
        }
    });
});
</script>
@endpush 
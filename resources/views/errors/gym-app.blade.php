@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                    </div>
                    
                    <h2 class="card-title mb-3">Oops! Something went wrong</h2>
                    
                    <p class="card-text text-muted mb-4">
                        {{ $message ?? 'An unexpected error occurred. Please try again.' }}
                    </p>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Go Back
                        </a>
                        
                        <a href="{{ route('frontend.home') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Go Home
                        </a>
                    </div>
                    
                    @if(config('app.debug'))
                        <div class="mt-4">
                            <details class="text-start">
                                <summary class="text-muted">Technical Details</summary>
                                <div class="mt-2 p-3 bg-light rounded">
                                    <small class="text-muted">
                                        <strong>Error Code:</strong> {{ $code ?? 'Unknown' }}<br>
                                        <strong>Request ID:</strong> {{ request()->id() ?? 'N/A' }}<br>
                                        <strong>Time:</strong> {{ now()->format('Y-m-d H:i:s') }}
                                    </small>
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
@if(session('error_details') && auth()->check() && auth()->user()->hasRole('Admin'))
    @php
        $errorDetails = session('error_details');
    @endphp
    
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-2">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error Details for Staff
                </h6>
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Error Code:</strong> {{ $errorDetails['error_code'] }}<br>
                        <strong>Category:</strong> {{ $errorDetails['category'] }}<br>
                        <strong>User Message:</strong> {{ $errorDetails['user_message'] }}<br>
                        <strong>Reference ID:</strong> <code>{{ $errorDetails['technical_details']['reference_id'] }}</code><br>
                        <strong>Timestamp:</strong> {{ \Carbon\Carbon::parse($errorDetails['technical_details']['timestamp'])->format('Y-m-d H:i:s') }}
                    </div>
                    <div class="col-md-6">
                        <strong>HTTP Code:</strong> {{ $errorDetails['technical_details']['http_code'] }}<br>
                        <strong>Context:</strong>
                        <ul class="list-unstyled ml-3">
                            @foreach($errorDetails['technical_details']['context'] as $key => $value)
                                <li><strong>{{ $key }}:</strong> 
                                    @if(is_array($value))
                                        <code>{{ json_encode($value) }}</code>
                                    @else
                                        {{ $value }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                
                @if(!empty($errorDetails['troubleshooting']))
                    <div class="mt-3">
                        <strong>Troubleshooting Steps:</strong>
                        <ol class="mb-2">
                            @foreach($errorDetails['troubleshooting'] as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ol>
                    </div>
                @endif
                
                @if(!empty($errorDetails['resolution_steps']))
                    <div class="mt-2">
                        <strong>Resolution Steps:</strong>
                        <ol class="mb-2">
                            @foreach($errorDetails['resolution_steps'] as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </div>
            
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

@if(session('error_code') && auth()->check() && auth()->user()->hasRole('Admin'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-2">
                    <i class="fas fa-info-circle mr-2"></i>
                    Error Reference
                </h6>
                <strong>Error Code:</strong> {{ session('error_code') }}<br>
                @if(session('error_reference'))
                    <strong>Reference ID:</strong> <code>{{ session('error_reference') }}</code><br>
                @endif
                <strong>Message:</strong> {{ session('error') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif 
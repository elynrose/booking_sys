@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Student Header -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            @if($student instanceof \App\Models\Child)
                                <i class="fas fa-child mr-2"></i>{{ $student->name }}
                            @else
                                <i class="fas fa-user mr-2"></i>{{ $student->name }}
                            @endif
                        </h4>
                        <div>
                            @if($schedule)
                                <a href="{{ route('frontend.trainer.class-details', $schedule) }}" class="btn btn-light btn-sm mr-2">
                                    <i class="fas fa-arrow-left"></i> Back to Class
                                </a>
                            @endif
                            <a href="{{ route('frontend.trainer.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Student Information</h5>
                            @if($student instanceof \App\Models\Child)
                                <p><strong>Name:</strong> {{ $student->name }}</p>
                                <p><strong>Age:</strong> {{ $student->age ?? 'Not specified' }} years</p>
                                <p><strong>Gender:</strong> {{ ucfirst($student->gender ?? 'Not specified') }}</p>
                                <p><strong>Date of Birth:</strong> 
                                    @if($student->date_of_birth)
                                        {{ \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') }}
                                    @else
                                        Not specified
                                    @endif
                                </p>
                                @if($student->notes)
                                    <p><strong>Notes:</strong> {{ $student->notes }}</p>
                                @endif
                            @else
                                <p><strong>Name:</strong> {{ $student->name }}</p>
                                <p><strong>Email:</strong> {{ $student->email }}</p>
                                <p><strong>Phone:</strong> {{ $student->phone ?? 'Not specified' }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5>Class Information</h5>
                            @if($schedule)
                                <p><strong>Class:</strong> {{ $schedule->title }}</p>
                                <p><strong>Date:</strong> 
                                    @if($schedule->start_date && $schedule->end_date)
                                        {{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') }} - 
                                        {{ \Carbon\Carbon::parse($schedule->end_date)->format('M d, Y') }}
                                    @else
                                        Not set
                                    @endif
                                </p>
                                <p><strong>Time:</strong> 
                                    @if($schedule->start_time && $schedule->end_time)
                                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - 
                                        {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                    @else
                                        Not set
                                    @endif
                                </p>
                                @if($booking)
                                    <p><strong>Payment Status:</strong> 
                                        <span class="badge badge-{{ $booking->payment_status === 'paid' ? 'success' : ($booking->payment_status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($booking->payment_status) }}
                                        </span>
                                    </p>
                                @endif
                            @else
                                <p class="text-muted">No specific class selected</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommendations Wall -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-comments mr-2"></i>
                        Recommendations & Progress Updates
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Create New Recommendation -->
                    <div class="mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Post New Recommendation</h6>
                                <form action="{{ route('frontend.recommendations.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="child_id" value="{{ $student instanceof \App\Models\Child ? $student->id : '' }}">
                                    <input type="hidden" name="user_id" value="{{ $student instanceof \App\Models\User ? $student->id : '' }}">
                                    
                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required placeholder="e.g., Great progress in swimming!">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="content">Content</label>
                                        <textarea class="form-control" id="content" name="content" rows="4" required placeholder="Share your observations, progress updates, or recommendations..."></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="type">Type</label>
                                                <select class="form-control" id="type" name="type" required>
                                                    <option value="progress">Progress</option>
                                                    <option value="improvement">Improvement</option>
                                                    <option value="achievement">Achievement</option>
                                                    <option value="general">General</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="priority">Priority</label>
                                                <select class="form-control" id="priority" name="priority" required>
                                                    <option value="low">Low</option>
                                                    <option value="medium">Medium</option>
                                                    <option value="high">High</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="attachments">Attachments (optional)</label>
                                        <input type="file" class="form-control-file" id="attachments" name="attachments[]" multiple>
                                        <small class="form-text text-muted">You can upload multiple files (max 10MB each)</small>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1" checked>
                                        <label class="form-check-label" for="is_public">
                                            Make this recommendation visible to the parent
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Post Recommendation
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendations Feed -->
                    <div class="recommendations-feed">
                        @if($recommendations->isEmpty())
                            <div class="text-center py-4">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No recommendations yet</h5>
                                <p class="text-muted">Start by posting your first recommendation above!</p>
                            </div>
                        @else
                            @foreach($recommendations as $recommendation)
                                <div class="card mb-3 recommendation-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="card-title mb-1">
                                                    <i class="fas {{ $recommendation->type_icon }} mr-2 text-{{ $recommendation->priority_color }}"></i>
                                                    {{ $recommendation->title }}
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    {{ $recommendation->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $recommendation->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu{{ $recommendation->id }}">
                                                    <a class="dropdown-item" href="{{ route('frontend.recommendations.edit', $recommendation) }}">
                                                        <i class="fas fa-edit mr-2"></i> Edit
                                                    </a>
                                                    <form action="{{ route('frontend.recommendations.destroy', $recommendation) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this recommendation?')">
                                                            <i class="fas fa-trash mr-2"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <p class="card-text">{{ $recommendation->content }}</p>
                                        
                                        @if($recommendation->attachments->isNotEmpty())
                                            <div class="attachments mb-3">
                                                <h6 class="text-muted mb-2">
                                                    <i class="fas fa-paperclip mr-1"></i> Attachments
                                                </h6>
                                                <div class="row">
                                                    @foreach($recommendation->attachments as $attachment)
                                                        <div class="col-md-4 mb-2">
                                                            <div class="attachment-item p-2 border rounded">
                                                                <i class="fas fa-file mr-2"></i>
                                                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="text-primary">
                                                                    {{ $attachment->original_filename }}
                                                                </a>
                                                                <small class="text-muted d-block">
                                                                    {{ number_format($attachment->file_size / 1024, 1) }} KB
                                                                </small>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <div class="recommendation-meta">
                                            <span class="badge badge-{{ $recommendation->priority_color }} mr-2">
                                                <i class="fas fa-flag mr-1"></i>
                                                {{ ucfirst($recommendation->priority) }} Priority
                                            </span>
                                            <span class="badge badge-{{ $recommendation->type === 'achievement' ? 'success' : ($recommendation->type === 'progress' ? 'info' : ($recommendation->type === 'improvement' ? 'warning' : 'secondary')) }}">
                                                <i class="fas fa-tag mr-1"></i>
                                                {{ ucfirst($recommendation->type) }}
                                            </span>
                                            @if($recommendation->is_public)
                                                <span class="badge badge-success ml-2">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    Visible to Parent
                                                </span>
                                            @else
                                                <span class="badge badge-secondary ml-2">
                                                    <i class="fas fa-eye-slash mr-1"></i>
                                                    Private
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.recommendation-card {
    border-left: 4px solid #28a745;
    transition: all 0.3s ease;
}

.recommendation-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.attachment-item {
    background-color: #f8f9fa;
    transition: background-color 0.3s ease;
}

.attachment-item:hover {
    background-color: #e9ecef;
}

.recommendation-meta {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
    margin-top: 1rem;
}

.card-header {
    border-bottom: none;
}

.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}
</style>
@endsection 
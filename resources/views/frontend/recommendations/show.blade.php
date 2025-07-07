@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="{{ $recommendation->type_icon }} mr-2 text-{{ $recommendation->priority_color }}"></i>
                            {{ $recommendation->title }}
                        </h3>
                        <div>
                            @if(auth()->user()->hasRole('Trainer'))
                                <a href="{{ route('frontend.recommendations.edit', $recommendation) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('frontend.recommendations.destroy', $recommendation) }}" 
                                      method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this recommendation?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('frontend.recommendations.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Recommendation Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <i class="fas fa-child mr-2"></i>
                                <strong>Student:</strong> {{ $recommendation->child->name }}
                            </p>
                            @if(auth()->user()->hasRole('Trainer'))
                                <p class="mb-1">
                                    <i class="fas fa-user mr-2"></i>
                                    <strong>Parent:</strong> {{ $recommendation->child->user->name }}
                                </p>
                            @else
                                <p class="mb-1">
                                    <i class="fas fa-user-tie mr-2"></i>
                                    <strong>Trainer:</strong> {{ $recommendation->trainer->name }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1">
                                <i class="fas fa-calendar mr-2"></i>
                                <strong>Posted:</strong> {{ $recommendation->created_at->format('M d, Y H:i') }}
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-tag mr-2"></i>
                                <strong>Type:</strong> {{ ucfirst($recommendation->type) }}
                            </p>
                        </div>
                    </div>

                    <!-- Priority Badge -->
                    <div class="mb-4">
                        <span class="badge badge-{{ $recommendation->priority_color }} px-3 py-2">
                            <i class="fas fa-flag mr-1"></i>
                            {{ ucfirst($recommendation->priority) }} Priority
                        </span>
                        @if(!$recommendation->isRead() && !auth()->user()->hasRole('Trainer'))
                            <span class="badge badge-danger ml-2 px-3 py-2">
                                <i class="fas fa-bell mr-1"></i>
                                New
                            </span>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="mb-4">
                        <h5>Recommendation Details</h5>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($recommendation->content)) !!}
                        </div>
                    </div>

                    <!-- Attachments -->
                    @if($recommendation->attachments->count() > 0)
                        <div class="mb-4">
                            <h5>
                                <i class="fas fa-paperclip mr-2"></i>
                                Attachments ({{ $recommendation->attachments->count() }})
                            </h5>
                            <div class="row">
                                @foreach($recommendation->attachments as $attachment)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-3">
                                                        @if($attachment->isImage())
                                                            <i class="fas fa-image fa-2x text-primary"></i>
                                                        @elseif($attachment->isPdf())
                                                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                                        @else
                                                            <i class="fas fa-file fa-2x text-secondary"></i>
                                                        @endif
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">{{ $attachment->original_filename }}</h6>
                                                        <small class="text-muted">{{ $attachment->formatted_size }}</small>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <a href="{{ $attachment->url }}" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                    @if(auth()->user()->hasRole('Trainer'))
                                                        <form action="{{ route('frontend.recommendations.delete-attachment', $attachment) }}" 
                                                              method="POST" 
                                                              class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to delete this attachment?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Responses Section -->
                    <div class="mb-4">
                        <h5>
                            <i class="fas fa-comments mr-2"></i>
                            Responses ({{ $recommendation->responses->count() }})
                        </h5>
                        
                        <!-- Display existing responses -->
                        @if($recommendation->responses->count() > 0)
                            <div class="responses-container">
                                @foreach($recommendation->responses as $response)
                                    <div class="card mb-3 {{ $response->user_id === auth()->id() ? 'border-primary' : 'border-secondary' }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <i class="fas fa-user mr-1"></i>
                                                        {{ $response->user->name }}
                                                        @if($response->user_id === auth()->id())
                                                            <span class="badge badge-primary ml-1">You</span>
                                                        @endif
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        {{ $response->created_at->format('M d, Y H:i') }}
                                                    </small>
                                                </div>
                                                @if($response->user_id === auth()->id())
                                                    <form action="{{ route('frontend.recommendations.responses.destroy', $response) }}" 
                                                          method="POST" 
                                                          class="d-inline" 
                                                          onsubmit="return confirm('Are you sure you want to delete this response?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                            <div class="response-content">
                                                {!! $response->formatted_content !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-comments fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No responses yet. Be the first to respond!</p>
                            </div>
                        @endif

                        <!-- Add Response Form -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-reply mr-2"></i>
                                    Add Your Response
                                </h6>
                                <form action="{{ route('frontend.recommendations.responses.store', $recommendation) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <textarea name="content" 
                                                  class="form-control @error('content') is-invalid @enderror" 
                                                  rows="4" 
                                                  placeholder="Share your thoughts, questions, or feedback about this recommendation..."
                                                  required>{{ old('content') }}</textarea>
                                        @error('content')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="is_public" name="is_public" value="1" {{ old('is_public', true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_public">
                                                Make this response public
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane mr-1"></i>
                                        Post Response
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Read Status -->
                    @if(!auth()->user()->hasRole('Trainer') && !$recommendation->isRead())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            This recommendation has been marked as read.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
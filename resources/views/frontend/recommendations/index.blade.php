@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title">
                                <i class="fas fa-comments"></i>
                                @if($child)
                                    Recommendations for {{ $child->name }}
                                @elseif(auth()->check() && auth()->user()->hasRole('Trainer'))
                                    My Recommendations
                                @else
                                    Recommendations for My Children
                                @endif
                            </h3>
                            @if($child)
                                <p class="text-muted mb-0">
                                    <i class="fas fa-child me-1"></i>
                                    Age: {{ $child->age }} years | 
                                    <i class="fas fa-user me-1"></i>
                                    Parent: {{ $child->user->name }}
                                </p>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            @if($child)
                                <a href="{{ route('frontend.children.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Children
                                </a>
                            @endif
                            @if(auth()->check() && auth()->user()->hasRole('Trainer'))
                                <a href="{{ route('frontend.recommendations.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Create New Recommendation
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($recommendations->count() > 0)
                        <div class="list-group">
                            @foreach($recommendations as $recommendation)
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="{{ $recommendation->type_icon }} mr-2 text-{{ $recommendation->priority_color }}"></i>
                                                <h5 class="mb-1">{{ $recommendation->title }}</h5>
                                                <span class="badge badge-{{ $recommendation->priority_color }} ml-2">
                                                    {{ ucfirst($recommendation->priority) }}
                                                </span>
                                                @if(!$recommendation->isRead() && auth()->check() && !auth()->user()->hasRole('Trainer'))
                                                    <span class="badge badge-danger ml-2">New</span>
                                                @endif
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1">
                                                        <i class="fas fa-child mr-2"></i>
                                                        <strong>Child:</strong> {{ $recommendation->child->name }}
                                                    </p>
                                                    @if(auth()->check() && auth()->user()->hasRole('Trainer'))
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
                                            
                                            <div class="mt-2">
                                                <p class="mb-2">{{ Str::limit($recommendation->content, 200) }}</p>
                                                
                                                @if($recommendation->attachments->count() > 0)
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-paperclip mr-1"></i>
                                                            {{ $recommendation->attachments->count() }} attachment(s)
                                                        </small>
                                                    </div>
                                                @endif
                                                @if($recommendation->responses->count() > 0)
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-comments mr-1"></i>
                                                            {{ $recommendation->responses->count() }} response(s)
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="ml-3">
                                            <div class="btn-group">
                                                <a href="{{ route('frontend.recommendations.show', $recommendation) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                @if(auth()->check() && auth()->user()->hasRole('Trainer'))
                                                    <a href="{{ route('frontend.recommendations.edit', $recommendation) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form action="{{ route('frontend.recommendations.destroy', $recommendation) }}" 
                                                          method="POST" 
                                                          class="d-inline" 
                                                          onsubmit="return confirm('Are you sure you want to delete this recommendation?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
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
                        
                        <div class="mt-4">
                            {{ $recommendations->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">
                                @if($child)
                                    No recommendations found for {{ $child->name }}
                                @else
                                    No recommendations found
                                @endif
                            </h5>
                            @if(auth()->check() && auth()->user()->hasRole('Trainer'))
                                <p class="text-muted">Start by creating your first recommendation for a student.</p>
                                <a href="{{ route('frontend.recommendations.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Recommendation
                                </a>
                            @else
                                <p class="text-muted">Your trainer will post recommendations here when available.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
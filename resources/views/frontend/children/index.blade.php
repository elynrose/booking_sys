@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">My Children</h4>
                    <a href="{{ route('frontend.children.create') }}" class="btn btn-light">
                        <i class="fas fa-plus me-2"></i>Add Child
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($children->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-child fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No children added yet</h5>
                            <p class="text-muted">Add your children to make booking easier</p>
                            <a href="{{ route('frontend.children.create') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-2"></i>Add Your First Child
                            </a>
                        </div>
                    @else
                        <div class="row">
                            @foreach($children as $child)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                @if($child->photo_url)
                                                    <img src="{{ $child->photo_url }}" 
                                                         alt="{{ $child->name }}" 
                                                         class="rounded-circle me-3" 
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="fas fa-child text-white"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h5 class="card-title mb-1">{{ $child->name }}</h5>
                                                    <p class="text-muted mb-0">
                                                        @if($child->date_of_birth)
                                                            {{ \Carbon\Carbon::parse($child->date_of_birth)->age }} years old
                                                        @else
                                                            Age not set
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <span class="badge bg-info">{{ ucfirst($child->gender) }}</span>
                                                @if($child->parent_consent)
                                                    <span class="badge bg-success">Consent Given</span>
                                                @else
                                                    <span class="badge bg-warning">Consent Pending</span>
                                                @endif
                                            </div>

                                            @if($child->address)
                                                <p class="text-muted small mb-2">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    {{ Str::limit($child->address, 50) }}
                                                </p>
                                            @endif

                                            @if($child->notes)
                                                <p class="text-muted small mb-3">
                                                    <i class="fas fa-sticky-note me-1"></i>
                                                    {{ Str::limit($child->notes, 80) }}
                                                </p>
                                            @endif

                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('frontend.children.edit', $child) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('frontend.recommendations.index', ['child_id' => $child->id]) }}" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-comments"></i>
                                                    </a>
                                                    <form action="{{ route('frontend.children.destroy', $child) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to remove this child?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $children->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
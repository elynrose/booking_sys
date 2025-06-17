@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Categories</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Category
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Categories List -->
                    <div class="list-group">
                        @foreach($categories as $category)
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="mb-1">{{ $category->name }}</h5>
                                            <span class="badge badge-info">
                                                <i class="fas fa-calendar mr-1"></i>
                                                {{ $category->schedules_count }} Schedules
                                            </span>
                                        </div>
                                        <p class="mb-1 text-muted">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            {{ $category->description ?: 'No description available' }}
                                        </p>
                                    </div>
                                    <div class="ml-3">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Trainers</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.trainers.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Trainer
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Trainers List -->
                    <div class="list-group">
                        @foreach($trainers as $trainer)
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <div class="mr-3">
                                            @if($trainer->profile_picture)
                                                <img src="{{ Storage::url($trainer->profile_picture) }}" alt="{{ $trainer->user->name }}" class="img-thumbnail rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <x-svg-placeholder type="trainer" text="No Photo" width="60px" height="60px" class="img-thumbnail rounded-circle" />
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h5 class="mb-1">{{ $trainer->user->name }}</h5>
                                                <span class="badge badge-{{ $trainer->is_active ? 'success' : 'danger' }}">
                                                    {{ $trainer->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1">
                                                        <i class="fas fa-envelope mr-2"></i>
                                                        <strong>Email:</strong> {{ $trainer->user->email }}
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1">
                                                        <i class="fas fa-money-bill mr-2"></i>
                                                        <strong>Payment Method:</strong> {{ ucfirst($trainer->payment_method) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.trainers.assign-student', $trainer) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-user-plus"></i> Assign Student
                                            </a>
                                            <a href="{{ route('admin.trainers.edit', $trainer) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.trainers.destroy', $trainer) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this trainer?')">
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
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Users</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New User
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Users List -->
                    <div class="list-group">
                        @forelse($users as $user)
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            @if($user->photo)
                                                <img src="{{ Storage::url($user->photo) }}" 
                                                     alt="{{ $user->name }}" 
                                                     class="rounded-circle mr-3"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-secondary mr-3 d-flex align-items-center justify-content-center"
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h5 class="mb-1">{{ $user->name }}</h5>
                                                <p class="mb-0 text-muted">
                                                    <i class="fas fa-envelope mr-1"></i>
                                                    {{ $user->email }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <i class="fas fa-calendar mr-2"></i>
                                                    <strong>Joined:</strong> {{ $user->created_at->format('M d, Y') }}
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fas fa-clock mr-2"></i>
                                                    <strong>Last Login:</strong> {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <i class="fas fa-user-tag mr-2"></i>
                                                    <strong>Role:</strong> 
                                                    <span class="badge badge-{{ $user->roles->first() ? 'primary' : 'secondary' }}">
                                                        {{ $user->roles->first() ? $user->roles->first()->name : 'No Role' }}
                                                    </span>
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fas fa-check-circle mr-2"></i>
                                                    <strong>Status:</strong>
                                                    <span class="badge badge-{{ $user->email_verified_at ? 'success' : 'warning' }}">
                                                        {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item">
                                <div class="text-center">No users found.</div>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                            </div>
                            {{ $users->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
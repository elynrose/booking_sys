@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Verified</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $verifiedUsers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Unverified</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $unverifiedUsers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">New (7 days)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $recentUsers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="search">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Search by name, email, phone, or member ID" 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="role_id">Role</label>
                                    <select class="form-control" id="role_id" name="role_id">
                                        <option value="">All Roles</option>
                                        @foreach($roles as $id => $name)
                                            <option value="{{ $id }}" {{ request('role_id') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="email_verified">Email Status</label>
                                    <select class="form-control" id="email_verified" name="email_verified">
                                        <option value="">All Users</option>
                                        <option value="verified" {{ request('email_verified') == 'verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="unverified" {{ request('email_verified') == 'unverified' ? 'selected' : '' }}>Unverified</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Search & Filter
                                        </button>
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Users List -->
                    <div class="list-group">
                        @forelse($users as $user)
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            @if($user->photo_url)
                                                <img src="{{ $user->photo_url }}" 
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
                                                    <strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y H:i') }}
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
                                                @if($user->member_id)
                                                <p class="mb-1">
                                                    <i class="fas fa-id-card mr-2"></i>
                                                    <strong>Member ID:</strong> 
                                                    <span class="badge badge-info">
                                                        {{ $user->member_id }}
                                                    </span>
                                                </p>
                                                @endif
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
                                            @if(!$user->email_verified_at)
                                                <form action="{{ route('admin.users.verify-email', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to verify this user\'s email?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check-circle"></i> Verify Email
                                                    </button>
                                                </form>
                                            @endif
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
                                <div class="text-center">
                                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No users found</h5>
                                    @if(request()->hasAny(['search', 'role_id', 'email_verified', 'start_date', 'end_date']))
                                        <p class="text-muted">Try adjusting your search criteria or filters.</p>
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                                            <i class="fas fa-times"></i> Clear All Filters
                                        </a>
                                    @endif
                                </div>
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
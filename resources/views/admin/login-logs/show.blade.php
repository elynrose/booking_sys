@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Login Log Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.login-logs.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Login Logs
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>User Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $loginLog->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $loginLog->user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Member ID:</strong></td>
                                    <td>{{ $loginLog->user->member_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $loginLog->user->phone_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Roles:</strong></td>
                                    <td>
                                        @foreach($loginLog->user->roles as $role)
                                            <span class="badge badge-primary">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Session Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($loginLog->status === 'login')
                                            <span class="badge badge-success">Login</span>
                                        @elseif($loginLog->status === 'logout')
                                            <span class="badge badge-info">Logout</span>
                                        @else
                                            <span class="badge badge-danger">Failed</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Login Time:</strong></td>
                                    <td>{{ $loginLog->login_time->format('M d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Logout Time:</strong></td>
                                    <td>
                                        @if($loginLog->logout_time)
                                            {{ $loginLog->logout_time->format('M d, Y H:i:s') }}
                                        @else
                                            <span class="badge badge-warning">Active Session</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Duration:</strong></td>
                                    <td>
                                        @if($loginLog->duration)
                                            {{ $loginLog->duration }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>IP Address:</strong></td>
                                    <td>{{ $loginLog->ip_address ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>User Agent Details</h5>
                            <div class="card">
                                <div class="card-body">
                                    <pre class="mb-0"><code>{{ $loginLog->user_agent ?? 'N/A' }}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($loginLog->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Notes</h5>
                            <div class="card">
                                <div class="card-body">
                                    {{ $loginLog->notes }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Recent Login History for {{ $loginLog->user->name }}</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Login Time</th>
                                            <th>Logout Time</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($loginLog->user->loginLogs()->latest('login_time')->limit(10)->get() as $log)
                                            <tr class="{{ $log->id === $loginLog->id ? 'table-primary' : '' }}">
                                                <td>{{ $log->login_time->format('M d, Y H:i:s') }}</td>
                                                <td>
                                                    @if($log->logout_time)
                                                        {{ $log->logout_time->format('M d, Y H:i:s') }}
                                                    @else
                                                        <span class="badge badge-warning">Active</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($log->duration)
                                                        {{ $log->duration }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($log->status === 'login')
                                                        <span class="badge badge-success">Login</span>
                                                    @elseif($log->status === 'logout')
                                                        <span class="badge badge-info">Logout</span>
                                                    @else
                                                        <span class="badge badge-danger">Failed</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ $log->ip_address ?? 'N/A' }}</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
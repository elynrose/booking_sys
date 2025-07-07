@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-comment"></i>
                            Recommendation Details
                        </h3>
                        <div>
                            <a href="{{ route('admin.recommendations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            @can('recommendation_delete')
                                <form action="{{ route('admin.recommendations.destroy', $recommendation) }}" 
                                      method="POST" 
                                      style="display: inline;"
                                      onsubmit="return confirm('Are you sure you want to delete this recommendation?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Recommendation Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Child Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $recommendation->child->name ?? 'Unknown Child' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Age:</strong></td>
                                    <td>{{ $recommendation->child->age ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Gender:</strong></td>
                                    <td>{{ ucfirst($recommendation->child->gender ?? 'N/A') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Trainer Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ optional($recommendation->trainer)->name ?? 'Unknown Trainer' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ optional($recommendation->trainer)->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td>{{ $recommendation->created_at->format('M d, Y g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Recommendation Content -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Recommendation Content</h5>
                            <div class="card">
                                <div class="card-body">
                                    <p>{{ $recommendation->content }}</p>
                                    
                                    @if($recommendation->attachments->count() > 0)
                                        <hr>
                                        <h6>Attachments:</h6>
                                        <div class="row">
                                            @foreach($recommendation->attachments as $attachment)
                                                <div class="col-md-3 mb-2">
                                                    <div class="card">
                                                        <div class="card-body text-center">
                                                            <i class="fas fa-file fa-2x text-muted mb-2"></i>
                                                            <p class="mb-1">{{ Str::limit($attachment->filename, 20) }}</p>
                                                            <a href="{{ Storage::url($attachment->file_path) }}" 
                                                               target="_blank" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Responses -->
                    <div class="row">
                        <div class="col-12">
                            <h5>Parent Responses ({{ $recommendation->responses->count() }})</h5>
                            
                            @if($recommendation->responses->count() > 0)
                                @foreach($recommendation->responses as $response)
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $response->user->name ?? 'Unknown Parent' }}</strong>
                                                    <small class="text-muted ml-2">{{ $response->created_at->format('M d, Y g:i A') }}</small>
                                                </div>
                                                <small class="text-muted">{{ $response->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p>{{ $response->content }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    No responses from parents yet.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
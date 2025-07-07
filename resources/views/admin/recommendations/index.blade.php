@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-comments"></i>
                        All Recommendations & Responses
                    </h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Child</th>
                                    <th>Trainer</th>
                                    <th>Recommendation</th>
                                    <th>Responses</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recommendations as $recommendation)
                                <tr>
                                    <td>
                                        <strong>{{ $recommendation->child->name ?? 'Unknown Child' }}</strong>
                                        @if($recommendation->child)
                                            <br><small class="text-muted">Age: {{ $recommendation->child->age ?? 'N/A' }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ optional($recommendation->trainer)->name ?? 'Unknown Trainer' }}
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;" title="{{ $recommendation->content }}">
                                            {{ Str::limit($recommendation->content, 100) }}
                                        </div>
                                        @if($recommendation->attachments->count() > 0)
                                            <small class="text-info">
                                                <i class="fas fa-paperclip"></i> {{ $recommendation->attachments->count() }} attachment(s)
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($recommendation->responses->count() > 0)
                                            <span class="badge badge-success">{{ $recommendation->responses->count() }} response(s)</span>
                                            <br>
                                            <small class="text-muted">
                                                Latest: {{ $recommendation->responses->sortByDesc('created_at')->first()->created_at->diffForHumans() }}
                                            </small>
                                        @else
                                            <span class="badge badge-secondary">No response</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $recommendation->created_at->format('M d, Y') }}
                                        <br>
                                        <small class="text-muted">{{ $recommendation->created_at->format('g:i A') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.recommendations.show', $recommendation) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('recommendation_delete')
                                                <form action="{{ route('admin.recommendations.destroy', $recommendation) }}" 
                                                      method="POST" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Are you sure you want to delete this recommendation?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $recommendations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-eye"></i>
                        Availability Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Date</h6>
                            <p class="h5 text-primary">{{ $availability->date->format('l, F d, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            @if($availability->status === 'available')
                                <span class="badge badge-success badge-pill">
                                    <i class="fas fa-check-circle mr-1"></i>Available
                                </span>
                            @elseif($availability->status === 'unavailable')
                                <span class="badge badge-danger badge-pill">
                                    <i class="fas fa-times-circle mr-1"></i>Unavailable
                                </span>
                            @else
                                <span class="badge badge-warning badge-pill">
                                    <i class="fas fa-clock mr-1"></i>Busy
                                </span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Start Time</h6>
                            <p class="h5">{{ \Carbon\Carbon::parse($availability->start_time)->format('g:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">End Time</h6>
                            <p class="h5">{{ \Carbon\Carbon::parse($availability->end_time)->format('g:i A') }}</p>
                        </div>
                    </div>

                    @if($availability->schedule)
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-muted">Associated Schedule</h6>
                                <p class="h5 text-info">{{ $availability->schedule->title }}</p>
                            </div>
                        </div>
                    @endif

                    @if($availability->notes)
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-muted">Notes</h6>
                                <p class="text-muted">{{ $availability->notes }}</p>
                            </div>
                        </div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('frontend.trainer.availability.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <div class="btn-group">
                            <a href="{{ route('frontend.trainer.availability.edit', $availability) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('frontend.trainer.availability.destroy', $availability) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this availability?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
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
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($children as $child)
                                        <tr>
                                            <td>{{ $child->name }}</td>
                                            <td>{{ $child->age }} years</td>
                                            <td>{{ ucfirst($child->gender) }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('frontend.children.edit', $child) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
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
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{{ __('app.children.my_children') }}</h4>
        <a href="{{ route('frontend.children.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>{{ __('app.actions.add') }} {{ __('app.children.title') }}
        </a>
    </div>

    @if($children->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-child fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('app.children.no_children') }}</h5>
                <p class="text-muted">{{ __('app.children.add_children_help') }}</p>
                <a href="{{ route('frontend.children.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>{{ __('app.actions.add') }} {{ __('app.children.title') }}
                </a>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('app.children.name') }}</th>
                                <th>{{ __('app.children.age') }}</th>
                                <th>{{ __('app.children.gender') }}</th>
                                <th>{{ __('app.children.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($children as $child)
                                <tr>
                                    <td>{{ $child->name }}</td>
                                    <td>
                                        @if($child->date_of_birth)
                                            {{ $child->date_of_birth->age }} {{ __('app.time.years') }}
                                        @else
                                            {{ __('app.status.n_a') }}
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($child->gender) }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('frontend.children.edit', $child->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> {{ __('app.actions.edit') }}
                                            </a>
                                            <form action="{{ route('frontend.children.destroy', $child->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('app.alerts.confirm_delete') }}')">
                                                    <i class="fas fa-trash"></i> {{ __('app.actions.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection 
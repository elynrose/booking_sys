@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">
            <i class="fas fa-table mr-2"></i>
            Map CSV Columns to Fields
        </h4>
    </div>
    
    <div class="card-body">
        <!-- Preview of CSV Data -->
        <div class="mb-4">
            <h5>CSV Preview (First 5 rows)</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            @foreach($headers as $index => $header)
                                <th>{{ $header }} (Column {{ $index }})</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lines as $line)
                            <tr>
                                @foreach($line as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Field Mapping Form -->
        <form action="{{ route('admin.schedules.process-csv') }}" method="POST">
            @csrf
            <input type="hidden" name="filename" value="{{ $filename }}">
            <input type="hidden" name="hasHeader" value="{{ $hasHeader ? '1' : '0' }}">
            
            <div class="mb-4">
                <h5>Map CSV Columns to Database Fields</h5>
                <p class="text-muted">Select which CSV column corresponds to each database field.</p>
                
                <div class="row">
                    @foreach($fillables as $field)
                        <div class="col-md-6 mb-3">
                            <label for="fields[{{ $field }}]" class="form-label">
                                <strong>{{ ucwords(str_replace('_', ' ', $field)) }}</strong>
                                @if(in_array($field, ['title', 'trainer_id', 'category_id', 'type', 'start_date', 'end_date', 'start_time', 'end_time', 'price', 'max_participants', 'status']))
                                    <span class="badge badge-danger">Required</span>
                                @endif
                            </label>
                            <select name="fields[{{ $field }}]" 
                                    class="form-control @error('fields.' . $field) is-invalid @enderror"
                                    {{ in_array($field, ['title', 'trainer_id', 'category_id', 'type', 'start_date', 'end_date', 'start_time', 'end_time', 'price', 'max_participants', 'status']) ? 'required' : '' }}>
                                <option value="">-- Select Column --</option>
                                @foreach($headers as $index => $header)
                                    <option value="{{ $index }}" 
                                            {{ old('fields.' . $field) == $index ? 'selected' : '' }}>
                                        {{ $header }} (Column {{ $index }})
                                    </option>
                                @endforeach
                            </select>
                            @error('fields.' . $field)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Field Help -->
            <div class="alert alert-info mb-4">
                <h6><i class="fas fa-info-circle mr-2"></i>Field Help</h6>
                <ul class="mb-0">
                    <li><strong>trainer_id</strong>: Use the trainer ID from the list below</li>
                    <li><strong>category_id</strong>: Use the category ID from the list below</li>
                    <li><strong>type</strong>: Must be either "group" or "private"</li>
                    <li><strong>start_date/end_date</strong>: Format should be YYYY-MM-DD</li>
                    <li><strong>start_time/end_time</strong>: Format should be HH:MM:SS</li>
                    <li><strong>price</strong>: Numeric value (e.g., 25.00)</li>
                    <li><strong>max_participants</strong>: Numeric value (e.g., 20)</li>
                    <li><strong>status</strong>: Should be "active" or "inactive"</li>
                    <li><strong>Boolean fields</strong> (is_featured, allow_unlimited_bookings, is_discounted): Use 1/0, true/false, or yes/no</li>
                </ul>
            </div>

            <!-- Available Trainers -->
            <div class="mb-4">
                <h6>Available Trainers</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trainers as $trainer)
                            <tr>
                                <td>{{ $trainer->id }}</td>
                                <td>{{ $trainer->user->name }}</td>
                                <td>{{ $trainer->user->email }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Available Categories -->
            <div class="mb-4">
                <h6>Available Categories</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->description ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.schedules.import') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Import
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check mr-2"></i>
                    Import Schedules
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 
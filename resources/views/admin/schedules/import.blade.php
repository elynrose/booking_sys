@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">
            <i class="fas fa-upload mr-2"></i>
            Import Schedules from CSV
        </h4>
    </div>
    
    <div class="card-body">
        <!-- Success Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Error Messages -->
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Import Errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Import Errors from Session -->
        @if(session('import_errors') && count(session('import_errors')) > 0)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Import Warnings:</strong>
                <ul class="mb-0 mt-2">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Import Statistics -->
        @if(session('import_stats'))
            @php $stats = session('import_stats'); @endphp
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-chart-bar mr-2"></i>
                <strong>Import Statistics:</strong>
                <ul class="mb-0 mt-2">
                    <li><strong>Imported:</strong> {{ $stats['imported'] ?? 0 }} schedules</li>
                    <li><strong>Skipped:</strong> {{ $stats['skipped'] ?? 0 }} rows</li>
                    @if(isset($stats['errors']) && count($stats['errors']) > 0)
                        <li><strong>Errors:</strong> {{ count($stats['errors']) }} issues found</li>
                    @endif
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Instructions -->
        <div class="alert alert-info mb-4">
            <h5><i class="fas fa-info-circle mr-2"></i>Instructions</h5>
            <ul class="mb-0">
                <li>Download the CSV template below to see the required format</li>
                <li>Fill in your schedule data following the template structure</li>
                <li>Upload your CSV file using the form below</li>
                <li>Review the data mapping before importing</li>
                <li>Required fields: title, start_time, end_time</li>
                <li>Optional fields: description, date, category, instructor, max_capacity, price, location, status</li>
            </ul>
        </div>

        <!-- Template Download -->
        <div class="mb-4">
            <h5>Step 1: Download Template</h5>
            <p class="text-muted">Download the CSV template to see the required format and field names.</p>
            <a href="{{ route('admin.schedules.download-template') }}" class="btn btn-outline-primary">
                <i class="fas fa-download mr-2"></i>
                Download CSV Template
            </a>
        </div>

        <!-- File Upload -->
        <div class="mb-4">
            <h5>Step 2: Upload CSV File</h5>
            <form action="{{ route('admin.schedules.process-csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label for="csv_file">Select CSV File</label>
                    <input type="file" 
                           class="form-control @error('csv_file') is-invalid @enderror" 
                           id="csv_file" 
                           name="csv_file" 
                           accept=".csv,.txt,.xlsx,.xls"
                           required>
                    @error('csv_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Maximum file size: 10MB. Supported formats: CSV, TXT, XLSX, XLS</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload mr-2"></i>
                    Import Schedules
                </button>
            </form>
        </div>

        <!-- Field Reference -->
        <div class="mt-4">
            <h5>Field Reference</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Field</th>
                            <th>Required</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>title</strong></td>
                            <td><span class="badge badge-danger">Yes</span></td>
                            <td>Text</td>
                            <td>Schedule title</td>
                            <td>Yoga Class</td>
                        </tr>
                        <tr>
                            <td><strong>description</strong></td>
                            <td><span class="badge badge-secondary">No</span></td>
                            <td>Text</td>
                            <td>Schedule description</td>
                            <td>Beginner yoga session</td>
                        </tr>
                        <tr>
                            <td><strong>start_time</strong></td>
                            <td><span class="badge badge-danger">Yes</span></td>
                            <td>Time</td>
                            <td>Start time (HH:MM, 9:00 AM, etc.)</td>
                            <td>09:00</td>
                        </tr>
                        <tr>
                            <td><strong>end_time</strong></td>
                            <td><span class="badge badge-danger">Yes</span></td>
                            <td>Time</td>
                            <td>End time (HH:MM, 10:00 AM, etc.)</td>
                            <td>10:00</td>
                        </tr>
                        <tr>
                            <td><strong>date</strong></td>
                            <td><span class="badge badge-secondary">No</span></td>
                            <td>Date</td>
                            <td>Schedule date (YYYY-MM-DD, DD/MM/YYYY, etc.)</td>
                            <td>2025-01-01</td>
                        </tr>
                        <tr>
                            <td><strong>category</strong></td>
                            <td><span class="badge badge-secondary">No</span></td>
                            <td>Text</td>
                            <td>Category name (will be created if doesn't exist)</td>
                            <td>Yoga</td>
                        </tr>
                        <tr>
                            <td><strong>instructor</strong></td>
                            <td><span class="badge badge-secondary">No</span></td>
                            <td>Text</td>
                            <td>Instructor name or email</td>
                            <td>John Doe</td>
                        </tr>
                        <tr>
                            <td><strong>max_capacity</strong></td>
                            <td><span class="badge badge-secondary">No</span></td>
                            <td>Number</td>
                            <td>Maximum participants (default: 10)</td>
                            <td>20</td>
                        </tr>
                        <tr>
                            <td><strong>price</strong></td>
                            <td><span class="badge badge-secondary">No</span></td>
                            <td>Number</td>
                            <td>Price in dollars (default: 0)</td>
                            <td>25.00</td>
                        </tr>
                        <tr>
                            <td><strong>location</strong></td>
                            <td><span class="badge badge-secondary">No</span></td>
                            <td>Text</td>
                            <td>Location</td>
                            <td>Studio A</td>
                        </tr>
                        <tr>
                            <td><strong>status</strong></td>
                            <td><span class="badge badge-secondary">No</span></td>
                            <td>Text</td>
                            <td>Status (default: active)</td>
                            <td>active</td>
                        </tr>
                            <td>Text</td>
                            <td>Location</td>
                            <td>Main Studio</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Available Trainers -->
        <div class="mt-4">
            <h5>Available Trainers</h5>
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
        <div class="mt-4">
            <h5>Available Categories</h5>
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
    </div>
</div>
@endsection 
@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i> Create New Recommendation
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('frontend.recommendations.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="form-group">
                            <label for="child_id">Student <span class="text-danger">*</span></label>
                            <select name="child_id" id="child_id" class="form-control @error('child_id') is-invalid @enderror" required>
                                <option value="">Select a student</option>
                                @foreach($children as $child)
                                    <option value="{{ $child->id }}" {{ old('child_id') == $child->id ? 'selected' : '' }}>
                                        {{ $child->name }} ({{ $child->user->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('child_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                                   value="{{ old('title') }}" required placeholder="Enter recommendation title">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="content">Content <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" rows="6" class="form-control @error('content') is-invalid @enderror" 
                                      required placeholder="Enter detailed recommendation content">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Type <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="">Select type</option>
                                        <option value="progress" {{ old('type') == 'progress' ? 'selected' : '' }}>Progress Report</option>
                                        <option value="improvement" {{ old('type') == 'improvement' ? 'selected' : '' }}>Areas for Improvement</option>
                                        <option value="achievement" {{ old('type') == 'achievement' ? 'selected' : '' }}>Achievement</option>
                                        <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>General</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="priority">Priority <span class="text-danger">*</span></label>
                                    <select name="priority" id="priority" class="form-control @error('priority') is-invalid @enderror" required>
                                        <option value="">Select priority</option>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_public" name="is_public" value="1" {{ old('is_public') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_public">
                                    Make this recommendation public to the parent
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="attachments">Attachments (Optional)</label>
                            <input type="file" name="attachments[]" id="attachments" class="form-control @error('attachments.*') is-invalid @enderror" 
                                   multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                            <small class="form-text text-muted">
                                You can upload multiple files. Maximum file size: 10MB each. 
                                Supported formats: PDF, DOC, DOCX, JPG, PNG, GIF
                            </small>
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Recommendation
                            </button>
                            <a href="{{ route('frontend.recommendations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
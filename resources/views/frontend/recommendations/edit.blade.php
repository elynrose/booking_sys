@extends('layouts.frontend')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Edit Recommendation
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('frontend.recommendations.update', $recommendation) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="child_id">Student <span class="text-danger">*</span></label>
                            <select name="child_id" id="child_id" class="form-control @error('child_id') is-invalid @enderror" required>
                                <option value="">Select a student</option>
                                @foreach($children as $child)
                                    <option value="{{ $child->id }}" {{ old('child_id', $recommendation->child_id) == $child->id ? 'selected' : '' }}>
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
                                   value="{{ old('title', $recommendation->title) }}" required placeholder="Enter recommendation title">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="content">Content <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" rows="6" class="form-control @error('content') is-invalid @enderror" 
                                      required placeholder="Enter detailed recommendation content">{{ old('content', $recommendation->content) }}</textarea>
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
                                        <option value="progress" {{ old('type', $recommendation->type) == 'progress' ? 'selected' : '' }}>Progress Report</option>
                                        <option value="improvement" {{ old('type', $recommendation->type) == 'improvement' ? 'selected' : '' }}>Areas for Improvement</option>
                                        <option value="achievement" {{ old('type', $recommendation->type) == 'achievement' ? 'selected' : '' }}>Achievement</option>
                                        <option value="general" {{ old('type', $recommendation->type) == 'general' ? 'selected' : '' }}>General</option>
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
                                        <option value="low" {{ old('priority', $recommendation->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority', $recommendation->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority', $recommendation->priority) == 'high' ? 'selected' : '' }}>High</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_public" name="is_public" value="1" 
                                       {{ old('is_public', $recommendation->is_public) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_public">
                                    Make this recommendation public to the parent
                                </label>
                            </div>
                        </div>

                        <!-- Existing Attachments -->
                        @if($recommendation->attachments->count() > 0)
                            <div class="form-group">
                                <label>Existing Attachments</label>
                                <div class="row">
                                    @foreach($recommendation->attachments as $attachment)
                                        <div class="col-md-6 mb-2">
                                            <div class="card">
                                                <div class="card-body p-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-2">
                                                            @if($attachment->isImage())
                                                                <i class="fas fa-image text-primary"></i>
                                                            @elseif($attachment->isPdf())
                                                                <i class="fas fa-file-pdf text-danger"></i>
                                                            @else
                                                                <i class="fas fa-file text-secondary"></i>
                                                            @endif
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <small class="d-block">{{ $attachment->original_filename }}</small>
                                                            <small class="text-muted">{{ $attachment->formatted_size }}</small>
                                                        </div>
                                                        <div>
                                                            <a href="{{ $attachment->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                            <form action="{{ route('frontend.recommendations.delete-attachment', $attachment) }}" 
                                                                  method="POST" 
                                                                  class="d-inline" 
                                                                  onsubmit="return confirm('Are you sure you want to delete this attachment?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="attachments">Add New Attachments (Optional)</label>
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
                                <i class="fas fa-save"></i> Update Recommendation
                            </button>
                            <a href="{{ route('frontend.recommendations.show', $recommendation) }}" class="btn btn-secondary">
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
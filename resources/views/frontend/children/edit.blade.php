@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit Child</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('frontend.children.update', $child) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Child's Name</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $child->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" 
                                   class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   id="date_of_birth" 
                                   name="date_of_birth" 
                                   value="{{ old('date_of_birth', $child->date_of_birth ? $child->date_of_birth->format('Y-m-d') : '') }}" 
                                   max="{{ date('Y-m-d') }}"
                                   required>
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input @error('gender') is-invalid @enderror" 
                                           type="radio" 
                                           name="gender" 
                                           id="gender_male" 
                                           value="male" 
                                           {{ old('gender', $child->gender) == 'male' ? 'checked' : '' }}
                                           required>
                                    <label class="form-check-label" for="gender_male">Male</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input @error('gender') is-invalid @enderror" 
                                           type="radio" 
                                           name="gender" 
                                           id="gender_female" 
                                           value="female" 
                                           {{ old('gender', $child->gender) == 'female' ? 'checked' : '' }}
                                           required>
                                    <label class="form-check-label" for="gender_female">Female</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input @error('gender') is-invalid @enderror" 
                                           type="radio" 
                                           name="gender" 
                                           id="gender_other" 
                                           value="other" 
                                           {{ old('gender', $child->gender) == 'other' ? 'checked' : '' }}
                                           required>
                                    <label class="form-check-label" for="gender_other">Other</label>
                                </div>
                            </div>
                            @error('gender')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3">{{ old('notes', $child->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('frontend.children.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Child</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
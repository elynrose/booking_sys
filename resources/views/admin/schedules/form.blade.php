            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $schedule->description ?? '') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="photo" class="form-label">Schedule Photo</label>
                <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo">
                @error('photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if(isset($schedule) && $schedule->photo)
                    <div class="mt-2">
                        <img src="{{ $schedule->photo_url }}" alt="Current photo" class="img-thumbnail" style="max-height: 200px">
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label for="trainer_id" class="form-label">Trainer</label>
                <select class="form-select @error('trainer_id') is-invalid @enderror" id="trainer_id" name="trainer_id">
                    <option value="">Select a trainer</option>
                    @foreach($trainers as $trainer)
                        <option value="{{ $trainer->id }}" {{ old('trainer_id', $schedule->trainer_id) == $trainer->id ? 'selected' : '' }}>{{ $trainer->name }}</option>
                    @endforeach
                </select>
                @error('trainer_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div> 
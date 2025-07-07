<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Trainer;
use App\Models\Category;
use App\Imports\SchedulesImport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleController extends Controller
{
    // Custom CSV import implementation

    public function index(Request $request)
    {
        $query = Schedule::with(['trainer.user']);

        // Apply date filters if provided
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        // Apply trainer filter if provided
        if ($request->filled('trainer_id')) {
            $query->where('trainer_id', $request->trainer_id);
        }

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply type filter if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $schedules = $query->latest()->paginate(10);

        // Stat cards
        $totalSchedules = Schedule::count();
        $activeSchedules = Schedule::where('status', '=', 'active')->count();
        $inactiveSchedules = Schedule::where('status', 'inactive')->count();
        $upcomingSchedules = Schedule::whereDate('start_date', '>', now())->count();

        // Get trainers for filter
        $trainers = Trainer::with('user')->where('is_active', true)->get();

        return view('admin.schedules.index', compact(
            'schedules',
            'totalSchedules',
            'activeSchedules',
            'inactiveSchedules',
            'upcomingSchedules',
            'trainers'
        ));
    }

    public function create()
    {
        $trainers = Trainer::with('user')->where('is_active', true)->get();
        $categories = Category::all();
        return view('admin.schedules.create', compact('trainers', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'trainer_id' => 'required|exists:trainers,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|string|in:group,private',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'price' => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1',
            'is_featured' => 'boolean',
            'status' => 'required|string',
            'allow_unlimited_bookings' => 'boolean',
            'is_discounted' => 'boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'location' => 'nullable|string|max:255',
            'discount_expiry_date' => 'nullable|date',
        ]);

        // Handle checkbox values
        $validated['allow_unlimited_bookings'] = $request->has('allow_unlimited_bookings');
        $validated['is_discounted'] = $request->has('is_discounted');
        $validated['discount_expiry_date'] = $request->filled('discount_expiry_date') ? Carbon::parse($request->discount_expiry_date) : null;

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('schedules', 'public');
        }

        Schedule::create($validated);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule created successfully.');
    }

    public function edit(Schedule $schedule)
    {
        $trainers = Trainer::with('user')->where('is_active', true)->get();
        $categories = Category::all();
        return view('admin.schedules.edit', compact('schedule', 'trainers', 'categories'));
    }

    public function show(Schedule $schedule)
    {
        $schedule->load(['trainer.user', 'category', 'bookings.user', 'availabilities']);
        return view('admin.schedules.show', compact('schedule'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        // Debug: Log the request data
        \Log::info('Schedule Update Request:', [
            'schedule_id' => $schedule->id,
            'has_file' => $request->hasFile('photo'),
            'file_name' => $request->file('photo') ? $request->file('photo')->getClientOriginalName() : 'No file',
            'file_size' => $request->file('photo') ? $request->file('photo')->getSize() : 0,
            'all_data' => $request->all()
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'trainer_id' => 'required|exists:trainers,id',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|string|in:group,private',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'price' => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1',
            'is_featured' => 'boolean',
            'status' => 'required|string',
            'allow_unlimited_bookings' => 'boolean',
            'is_discounted' => 'boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'location' => 'nullable|string|max:255',
            'discount_expiry_date' => 'nullable|date',
        ]);

        // Debug: Log validation results
        \Log::info('Schedule Update Validation:', [
            'validated_data' => $validated,
            'has_photo_in_validated' => isset($validated['photo'])
        ]);

        // Handle checkbox values
        $validated['allow_unlimited_bookings'] = $request->has('allow_unlimited_bookings');
        $validated['is_discounted'] = $request->has('is_discounted');
        $validated['discount_expiry_date'] = $request->filled('discount_expiry_date') ? Carbon::parse($request->discount_expiry_date) : null;

        if ($request->hasFile('photo')) {
            // Debug: Log photo upload process
            \Log::info('Processing photo upload:', [
                'old_photo' => $schedule->photo,
                'new_file_name' => $request->file('photo')->getClientOriginalName()
            ]);

            // Delete old photo if exists
            if ($schedule->photo) {
                Storage::disk('public')->delete($schedule->photo);
                \Log::info('Deleted old photo:', ['old_photo_path' => $schedule->photo]);
            }
            
            $validated['photo'] = $request->file('photo')->store('schedules', 'public');
            \Log::info('Stored new photo:', ['new_photo_path' => $validated['photo']]);
        } else {
            \Log::info('No photo file uploaded');
        }

        $schedule->update($validated);

        \Log::info('Schedule updated successfully:', [
            'schedule_id' => $schedule->id,
            'new_photo' => $schedule->fresh()->photo
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect()
            ->route('admin.schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }

    /**
     * Show CSV import form
     */
    public function importForm()
    {
        $trainers = Trainer::with('user')->where('is_active', true)->get();
        $categories = Category::all();
        return view('admin.schedules.import', compact('trainers', 'categories'));
    }

    /**
     * Process CSV import for schedules using Laravel Excel
     */
    public function processCsvImport(Request $request)
    {
        try {
            \Log::info('CSV Import started', [
                'file' => $request->file('csv_file') ? $request->file('csv_file')->getClientOriginalName() : 'No file',
                'size' => $request->file('csv_file') ? $request->file('csv_file')->getSize() : 0
            ]);

            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // 10MB max
            ], [
                'csv_file.required' => 'Please select a file to import.',
                'csv_file.file' => 'The uploaded file is not valid.',
                'csv_file.mimes' => 'The file must be a CSV, Excel, or text file.',
                'csv_file.max' => 'The file size must not exceed 10MB.',
            ]);

            $file = $request->file('csv_file');
            \Log::info('File validated successfully', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);
            
            // Store the file temporarily
            $path = $file->store('temp_imports');
            \Log::info('File stored temporarily', ['path' => $path]);
            
            // Import using Laravel Excel
            $import = new SchedulesImport();
            \Log::info('Starting Excel import');
            Excel::import($import, $path);
            \Log::info('Excel import completed');
            
            // Get import statistics
            $stats = $import->getStats();
            \Log::info('Import statistics', $stats);
            
            // Clean up temporary file
            Storage::delete($path);
            \Log::info('Temporary file cleaned up');
            
            // Prepare success/error messages
            $message = "Import completed successfully! ";
            $message .= "{$stats['imported']} schedules imported";
            
            if ($stats['skipped'] > 0) {
                $message .= ", {$stats['skipped']} rows skipped";
            }
            
            if (!empty($stats['errors'])) {
                $message .= ". Some errors occurred during import.";
            }
            
            return redirect()->route('admin.schedules.index')
                ->with('success', $message)
                ->with('import_errors', $stats['errors'])
                ->with('import_stats', $stats);
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('CSV Import validation failed', [
                'errors' => $e->errors(),
                'file' => $request->file('csv_file') ? $request->file('csv_file')->getClientOriginalName() : 'No file'
            ]);
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            \Log::error('Excel validation failed', [
                'errors' => $e->failures(),
                'file' => $request->file('csv_file') ? $request->file('csv_file')->getClientOriginalName() : 'No file'
            ]);
            
            $errorMessages = [];
            foreach ($e->failures() as $failure) {
                $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            return back()->withErrors(['csv_file' => 'Import validation failed: ' . implode('; ', $errorMessages)])->withInput();
            
        } catch (\Exception $e) {
            \Log::error('CSV Import failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'Import failed: ' . $e->getMessage();
            
            // Provide more specific error messages for common issues
            if (str_contains($e->getMessage(), 'No such file or directory')) {
                $errorMessage = 'Import failed: Could not read the uploaded file. Please ensure the file is not corrupted.';
            } elseif (str_contains($e->getMessage(), 'Permission denied')) {
                $errorMessage = 'Import failed: Permission denied. Please check file permissions.';
            } elseif (str_contains($e->getMessage(), 'memory')) {
                $errorMessage = 'Import failed: File too large or complex. Please try with a smaller file.';
            } elseif (str_contains($e->getMessage(), 'database')) {
                $errorMessage = 'Import failed: Database error. Please check your database connection.';
            }
            
            return back()->withErrors(['csv_file' => $errorMessage])->withInput();
        }
    }

    /**
     * Parse CSV file for import
     */
    public function parseCsvImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->path();
        $hasHeader = $request->input('header', false) ? true : false;

        $reader = new \SpreadsheetReader($path);
        $headers = $reader->current();
        $lines = [];

        $i = 0;
        while ($reader->next() !== false && $i < 5) {
            $lines[] = $reader->current();
            $i++;
        }

        $filename = Str::random(10) . '.csv';
        $file->storeAs('csv_import', $filename);

        $model = new Schedule();
        $fillables = $model->getFillable();

        $trainers = Trainer::with('user')->where('is_active', true)->get();
        $categories = Category::all();

        return view('admin.schedules.parse-csv', compact(
            'headers', 
            'filename', 
            'fillables', 
            'hasHeader', 
            'lines', 
            'trainers',
            'categories'
        ));
    }

    /**
     * Process individual schedule data from CSV
     */
    private function processScheduleData($data)
    {
        try {
            // Required fields validation
            $required = ['title', 'trainer_id', 'category_id', 'type', 'start_date', 'end_date', 'start_time', 'end_time', 'price', 'max_participants', 'status'];
            
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['error' => "Missing required field: {$field}"];
                }
            }

            // Validate trainer exists
            $trainer = Trainer::find($data['trainer_id']);
            if (!$trainer) {
                return ['error' => "Trainer ID {$data['trainer_id']} not found"];
            }

            // Validate category exists
            $category = Category::find($data['category_id']);
            if (!$category) {
                return ['error' => "Category ID {$data['category_id']} not found"];
            }

            // Process dates
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            
            if ($endDate < $startDate) {
                return ['error' => "End date must be after start date"];
            }

            // Process times
            $startTime = Carbon::parse($data['start_time']);
            $endTime = Carbon::parse($data['end_time']);
            
            if ($endTime <= $startTime) {
                return ['error' => "End time must be after start time"];
            }

            // Process boolean fields
            $isFeatured = isset($data['is_featured']) && in_array(strtolower($data['is_featured']), ['1', 'true', 'yes', 'on']);
            $allowUnlimitedBookings = isset($data['allow_unlimited_bookings']) && in_array(strtolower($data['allow_unlimited_bookings']), ['1', 'true', 'yes', 'on']);
            $isDiscounted = isset($data['is_discounted']) && in_array(strtolower($data['is_discounted']), ['1', 'true', 'yes', 'on']);

            // Generate slug
            $slug = Str::slug($data['title']);

            return [
                'title' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'] ?? '',
                'trainer_id' => $data['trainer_id'],
                'category_id' => $data['category_id'],
                'type' => $data['type'],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'price' => floatval($data['price']),
                'max_participants' => intval($data['max_participants']),
                'is_featured' => $isFeatured,
                'status' => $data['status'],
                'allow_unlimited_bookings' => $allowUnlimitedBookings,
                'is_discounted' => $isDiscounted,
                'discount_percentage' => isset($data['discount_percentage']) ? floatval($data['discount_percentage']) : null,
                'location' => $data['location'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

        } catch (\Exception $e) {
            return ['error' => "Data processing error: " . $e->getMessage()];
        }
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $filename = 'schedules_import_template.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'title',
                'description', 
                'trainer_id',
                'category_id',
                'type',
                'start_date',
                'end_date',
                'start_time',
                'end_time',
                'price',
                'max_participants',
                'is_featured',
                'status',
                'allow_unlimited_bookings',
                'is_discounted',
                'discount_percentage',
                'location'
            ]);
            
            // Example row
            fputcsv($file, [
                'Yoga Class',
                'Beginner yoga session',
                '1',
                '1',
                'group',
                '2025-01-01',
                '2025-12-31',
                '09:00:00',
                '10:00:00',
                '25.00',
                '20',
                '0',
                'active',
                '0',
                '0',
                '0',
                'Main Studio'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 
<?php

namespace App\Imports;

use App\Models\Schedule;
use App\Models\User;
use App\Models\Category;
use App\Models\Trainer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SchedulesImport implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    WithBatchInserts, 
    WithChunkReading,
    SkipsOnError,
    SkipsEmptyRows,
    WithStartRow
{
    private $errors = [];
    private $importedCount = 0;
    private $skippedCount = 0;

    public function __construct()
    {
        $this->errors = [];
    }

    /**
     * @param array $row
     */
    public function model(array $row)
    {
        try {
            // Skip if required fields are empty
            if (empty($row['title']) || empty($row['start_time']) || empty($row['end_time'])) {
                $this->skippedCount++;
                $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount) . ": Missing required fields (title, start_time, or end_time)";
                return null;
            }

            // Find or create category
            $category = null;
            if (!empty($row['category'])) {
                try {
                    $category = Category::firstOrCreate(
                        ['name' => trim($row['category'])],
                        [
                            'name' => trim($row['category']),
                            'description' => 'Imported from CSV'
                        ]
                    );
                } catch (\Exception $e) {
                    $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount + 1) . ": Failed to create category '{$row['category']}': " . $e->getMessage();
                    $this->skippedCount++;
                    return null;
                }
            }

            // Find trainer by name or email
            $trainer = null;
            if (!empty($row['instructor'])) {
                try {
                    $trainer = Trainer::whereHas('user', function($query) use ($row) {
                        $query->where('name', 'like', '%' . trim($row['instructor']) . '%')
                              ->orWhere('email', trim($row['instructor']));
                    })->first();
                    
                    if (!$trainer) {
                        $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount + 1) . ": Instructor '{$row['instructor']}' not found";
                    }
                } catch (\Exception $e) {
                    $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount + 1) . ": Error finding trainer '{$row['instructor']}': " . $e->getMessage();
                }
            }
            
            // If no trainer found, use the first available trainer as default
            if (!$trainer) {
                try {
                    $trainer = Trainer::where('is_active', true)->first();
                    if (!$trainer) {
                        $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount + 1) . ": No trainer found and no default trainer available";
                        $this->skippedCount++;
                        return null;
                    } else {
                        $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount + 1) . ": Using default trainer (no instructor specified)";
                    }
                } catch (\Exception $e) {
                    $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount + 1) . ": Error finding default trainer: " . $e->getMessage();
                    $this->skippedCount++;
                    return null;
                }
            }

            // Parse dates
            try {
                $startDate = $this->parseDate($row['date'] ?? date('Y-m-d'));
                $startTime = $this->parseTime($row['start_time']);
                $endTime = $this->parseTime($row['end_time']);
                
                // Validate time logic
                $startDateTime = Carbon::parse($startDate . ' ' . $startTime);
                $endDateTime = Carbon::parse($startDate . ' ' . $endTime);
                
                if ($endDateTime <= $startDateTime) {
                    $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount + 1) . ": End time must be after start time";
                    $this->skippedCount++;
                    return null;
                }
            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount + 1) . ": Error parsing date/time: " . $e->getMessage();
                $this->skippedCount++;
                return null;
            }

            // Validate numeric fields
            $maxParticipants = intval($row['max_capacity'] ?? 10);
            $price = floatval($row['price'] ?? 0);
            
            if ($maxParticipants <= 0) {
                $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount + 1) . ": Max capacity must be greater than 0";
                $this->skippedCount++;
                return null;
            }
            
            if ($price < 0) {
                $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount + 1) . ": Price cannot be negative";
                $this->skippedCount++;
                return null;
            }

            // Create schedule
            try {
                $schedule = new Schedule([
                    'title' => trim($row['title']),
                    'description' => trim($row['description'] ?? ''),
                    'start_date' => $startDate,
                    'end_date' => $startDate, // Same day for now
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'max_participants' => $maxParticipants,
                    'price' => $price,
                    'location' => trim($row['location'] ?? ''),
                    'status' => $row['status'] ?? 'active',
                    'category_id' => $category ? $category->id : null,
                    'trainer_id' => $trainer ? $trainer->id : null,
                ]);

                $this->importedCount++;
                return $schedule;

            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount + 1) . ": Error creating schedule: " . $e->getMessage();
                $this->skippedCount++;
                return null;
            }

        } catch (\Exception $e) {
            $this->errors[] = "Row " . ($this->importedCount + $this->skippedCount + 1) . ": Unexpected error: " . $e->getMessage();
            $this->skippedCount++;
            return null;
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'date' => 'nullable',
            'category' => 'nullable|string|max:255',
            'instructor' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'max_capacity' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,cancelled',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'title.required' => 'Title is required',
            'start_time.required' => 'Start time is required',
            'end_time.required' => 'End time is required',
            'max_capacity.integer' => 'Max capacity must be a number',
            'price.numeric' => 'Price must be a number',
        ];
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2; // Skip header row
    }

    /**
     * Parse date string
     */
    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return now()->format('Y-m-d');
        }

        // Try different date formats
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'd-m-Y', 'm-d-Y'];
        
        foreach ($formats as $format) {
            $date = Carbon::createFromFormat($format, trim($dateString));
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        // If all formats fail, return today
        return now()->format('Y-m-d');
    }

    /**
     * Parse time string
     */
    private function parseTime($timeString)
    {
        if (empty($timeString)) {
            return '09:00:00';
        }

        $time = trim($timeString);

        // Try different time formats
        $formats = ['H:i:s', 'H:i', 'g:i A', 'g:i a', 'G:i', 'G:i:s'];
        
        foreach ($formats as $format) {
            $parsedTime = Carbon::createFromFormat($format, $time);
            if ($parsedTime !== false) {
                return $parsedTime->format('H:i:s');
            }
        }

        // If all formats fail, return default
        return '09:00:00';
    }

    /**
     * Handle import errors
     */
    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
        $this->skippedCount++;
    }

    /**
     * Get import statistics
     */
    public function getStats()
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errors,
        ];
    }
} 
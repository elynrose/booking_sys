<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trainer;
use App\Models\TrainerAvailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SiteSettings;

class TrainerCalendarController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $siteTimezone = SiteSettings::getTimezone();
        
        // Get current month or requested month
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $currentDate = Carbon::createFromDate($year, $month, 1, $siteTimezone);
        
        // Get previous and next month for navigation
        $previousMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
        
        // Get trainers with their availabilities for the current month
        $trainers = Trainer::with(['user', 'availabilities' => function($query) use ($currentDate) {
            $query->whereBetween('date', [
                $currentDate->copy()->startOfMonth(),
                $currentDate->copy()->endOfMonth()
            ]);
        }])->get();
        
        // Generate colors for each trainer
        $trainerColors = $this->generateTrainerColors($trainers);
        
        // Generate calendar data
        $calendarData = $this->generateCalendarData($currentDate, $trainers);
        
        return view('admin.trainer-calendar.index', compact(
            'currentDate', 
            'previousMonth', 
            'nextMonth', 
            'trainers', 
            'calendarData',
            'trainerColors'
        ));
    }
    
    private function generateCalendarData($date, $trainers)
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        
        // Get the first day of the month and the last day
        $firstDayOfMonth = $startOfMonth->copy()->startOfWeek();
        $lastDayOfMonth = $endOfMonth->copy()->endOfWeek();
        
        $calendar = [];
        $currentDay = $firstDayOfMonth->copy();
        
        while ($currentDay->lte($lastDayOfMonth)) {
            $week = [];
            
            for ($i = 0; $i < 7; $i++) {
                $dayData = [
                    'date' => $currentDay->copy(),
                    'isCurrentMonth' => $currentDay->month === $date->month,
                    'isToday' => $currentDay->isToday(),
                    'trainers' => []
                ];
                
                // Get trainers available on this day
                foreach ($trainers as $trainer) {
                    $dayAvailabilities = $trainer->availabilities->filter(function($availability) use ($currentDay) {
                        $availabilityDate = $availability->date instanceof \Carbon\Carbon
                            ? $availability->date
                            : \Carbon\Carbon::parse($availability->date);
                        return $availabilityDate->format('Y-m-d') === $currentDay->format('Y-m-d');
                    });
                    
                    if ($dayAvailabilities->count() > 0) {
                        $dayData['trainers'][] = [
                            'trainer' => $trainer,
                            'availabilities' => $dayAvailabilities
                        ];
                    }
                }
                
                $week[] = $dayData;
                $currentDay->addDay();
            }
            
            $calendar[] = $week;
        }
        
        return $calendar;
    }
    
    /**
     * Generate different colors for each trainer
     */
    private function generateTrainerColors($trainers)
    {
        $colors = [
            '#d4edda', // Light green
            '#d1ecf1', // Light cyan
            '#d4edda', // Light green (duplicate, will be replaced)
            '#fff3cd', // Light yellow
            '#f8d7da', // Light red
            '#e2e3e5', // Light gray
            '#d1ecf1', // Light cyan (duplicate, will be replaced)
            '#f8d7da', // Light red (duplicate, will be replaced)
            '#fff3cd', // Light yellow (duplicate, will be replaced)
            '#e2e3e5', // Light gray (duplicate, will be replaced)
            '#d4edda', // Light green (duplicate, will be replaced)
            '#f8d7da', // Light red (duplicate, will be replaced)
        ];
        
        // Create more unique colors
        $uniqueColors = [
            '#d4edda', // Light green
            '#d1ecf1', // Light cyan
            '#fff3cd', // Light yellow
            '#f8d7da', // Light red
            '#e2e3e5', // Light gray
            '#cce5ff', // Light blue
            '#d4edda', // Light green
            '#f8d7da', // Light red
            '#fff3cd', // Light yellow
            '#e2e3e5', // Light gray
            '#d1ecf1', // Light cyan
            '#cce5ff', // Light blue
        ];
        
        $trainerColors = [];
        $colorIndex = 0;
        
        foreach ($trainers as $trainer) {
            $trainerColors[$trainer->id] = $uniqueColors[$colorIndex % count($uniqueColors)];
            $colorIndex++;
        }
        
        return $trainerColors;
    }
    
    public function getAvailabilityData(Request $request)
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        
        $siteTimezone = \App\Models\SiteSettings::getTimezone();
        $date = $request->input('date');
        $trainerId = $request->input('trainer_id');
        
        if (!$date) {
            return response()->json(['error' => 'Date is required'], 400);
        }
        
        $query = TrainerAvailability::with(['trainer.user', 'schedule'])
            ->where('date', $date)
            ->where('status', 'available');
            
        if ($trainerId) {
            $query->where('trainer_id', $trainerId);
        }
        
        $availabilities = $query->orderBy('start_time')->get();
        
        return response()->json([
            'availabilities' => $availabilities->map(function($availability) {
                return [
                    'id' => $availability->id,
                    'trainer_name' => $availability->trainer->user->name,
                    'schedule_title' => $availability->schedule->title ?? 'General Availability',
                    'start_time' => $availability->start_time,
                    'end_time' => $availability->end_time,
                    'location' => $availability->schedule->location ?? 'Not specified'
                ];
            })
        ]);
    }
} 
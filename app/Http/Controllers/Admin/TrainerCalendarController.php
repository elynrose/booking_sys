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

class TrainerCalendarController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('trainer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $siteTimezone = \App\Models\SiteSettings::getTimezone();
        
        // Get the requested month/year or default to current month
        $year = $request->input('year', Carbon::now($siteTimezone)->year);
        $month = $request->input('month', Carbon::now($siteTimezone)->month);
        
        $currentDate = Carbon::createFromDate($year, $month, 1, $siteTimezone);
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();
        
        // Get all trainers with their availability for the month
        $trainers = Trainer::with(['user', 'availabilities' => function($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                  ->where('status', 'available')
                  ->orderBy('date')
                  ->orderBy('start_time');
        }])->get();
        
        // Get calendar data for the month
        $calendarData = $this->generateCalendarData($currentDate, $trainers);
        
        // Get navigation data
        $previousMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
        
        return view('admin.trainer-calendar.index', compact(
            'trainers',
            'calendarData',
            'currentDate',
            'previousMonth',
            'nextMonth'
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
                    $dayAvailabilities = $trainer->availabilities->where('date', $currentDay->format('Y-m-d'));
                    
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
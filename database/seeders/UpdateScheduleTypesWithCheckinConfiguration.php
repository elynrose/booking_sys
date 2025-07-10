<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ScheduleType;

class UpdateScheduleTypesWithCheckinConfiguration extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultConfigurations = [
            'group' => [
                'max_checkins_per_day' => 1,
                'requires_trainer_availability' => false,
                'allows_unlimited_checkins' => false,
                'checkin_window_minutes' => 0,
                'late_checkin_allowed' => true,
                'auto_checkout_enabled' => true,
                'session_tracking_enabled' => true,
            ],
            'private' => [
                'max_checkins_per_day' => 1,
                'requires_trainer_availability' => true,
                'allows_unlimited_checkins' => false,
                'checkin_window_minutes' => 15,
                'late_checkin_allowed' => false,
                'auto_checkout_enabled' => true,
                'session_tracking_enabled' => true,
            ],
            'unlimited' => [
                'max_checkins_per_day' => 999,
                'requires_trainer_availability' => false,
                'allows_unlimited_checkins' => true,
                'checkin_window_minutes' => 0,
                'late_checkin_allowed' => true,
                'auto_checkout_enabled' => true,
                'session_tracking_enabled' => false,
            ],
            'drop-in' => [
                'max_checkins_per_day' => 1,
                'requires_trainer_availability' => false,
                'allows_unlimited_checkins' => false,
                'checkin_window_minutes' => 30,
                'late_checkin_allowed' => true,
                'auto_checkout_enabled' => true,
                'session_tracking_enabled' => true,
            ],
        ];

        foreach ($defaultConfigurations as $slug => $config) {
            $scheduleType = ScheduleType::where('slug', $slug)->first();
            
            if ($scheduleType) {
                $scheduleType->update($config);
                $this->command->info("Updated schedule type '{$slug}' with check-in configuration.");
            } else {
                $this->command->warn("Schedule type '{$slug}' not found.");
            }
        }

        // Update any other schedule types with default group configuration
        $otherTypes = ScheduleType::whereNotIn('slug', array_keys($defaultConfigurations))->get();
        
        foreach ($otherTypes as $scheduleType) {
            $scheduleType->update($defaultConfigurations['group']);
            $this->command->info("Updated schedule type '{$scheduleType->slug}' with default group configuration.");
        }

        $this->command->info('Schedule type check-in configurations updated successfully!');
    }
}

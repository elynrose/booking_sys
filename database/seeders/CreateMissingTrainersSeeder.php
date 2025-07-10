<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Trainer;
use App\Models\User;

class CreateMissingTrainersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create trainer records for users 13-17 that are referenced in schedules
        $userIds = [13, 14, 15, 16, 17];
        
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            
            if ($user && !Trainer::where('user_id', $userId)->exists()) {
                Trainer::create([
                    'user_id' => $userId,
                    'profile_picture' => null,
                    'bio' => 'Professional trainer with expertise in various sports and fitness activities.',
                    'payment_method' => 'paypal',
                    'payment_details' => $user->email,
                    'is_active' => true,
                ]);
                
                $this->command->info("Created trainer record for user ID: {$userId} ({$user->name})");
            }
        }
        
        $this->command->info('Missing trainer records created successfully!');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SiteSettings;

class StripeSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = SiteSettings::first();
        
        if ($settings) {
            $settings->update([
                'stripe_enabled' => true,
                'stripe_publishable_key' => env('STRIPE_KEY'),
                'stripe_secret_key' => env('STRIPE_SECRET'),
                'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
                'stripe_currency' => 'usd'
            ]);
            
            $this->command->info('Stripe settings updated successfully!');
        } else {
            $this->command->error('No site settings found. Please run the main seeder first.');
        }
    }
}

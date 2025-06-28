<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\SiteSettings;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        // Get site settings
        $siteSettings = SiteSettings::first();

        // Get top 5 group classes (active, featured, and upcoming)
        $groupClasses = Schedule::with(['trainer.user', 'category'])
            ->where('type', 'group')
            ->where('status', 'active')
            ->orderBy('is_featured', 'desc')
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        // Get top 5 private classes (active, featured, and upcoming)
        $privateClasses = Schedule::with(['trainer.user', 'category'])
            ->where('type', 'private')
            ->where('status', 'active')
            ->orderBy('is_featured', 'desc')
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        return view('welcome', compact('siteSettings', 'groupClasses', 'privateClasses'));
    }
}

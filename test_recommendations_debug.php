<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Recommendations Page...\n";

try {
    // Test 1: Check if we can find a trainer user
    echo "\n1. Checking for trainer users...\n";
    $trainerUser = \App\Models\User::whereHas('roles', function($query) {
        $query->where('name', 'Trainer');
    })->first();
    
    if (!$trainerUser) {
        echo "ERROR: No trainer user found!\n";
        exit(1);
    }
    
    echo "✓ Trainer user found: {$trainerUser->name} ({$trainerUser->email})\n";
    
    // Test 2: Check if we can find a child
    echo "\n2. Checking for children...\n";
    $child = \App\Models\Child::first();
    
    if (!$child) {
        echo "ERROR: No children found!\n";
        exit(1);
    }
    
    echo "✓ Child found: {$child->name} (Parent: {$child->user->name})\n";
    
    // Test 3: Test creating a recommendation
    echo "\n3. Testing recommendation creation...\n";
    $recommendation = \App\Models\Recommendation::create([
        'trainer_id' => $trainerUser->id,
        'child_id' => $child->id,
        'title' => 'Test Recommendation',
        'content' => 'This is a test recommendation content.',
        'type' => 'progress',
        'priority' => 'medium',
        'is_public' => true,
    ]);
    
    echo "✓ Recommendation created successfully (ID: {$recommendation->id})\n";
    
    // Test 4: Test the index query for trainers
    echo "\n4. Testing trainer recommendations query...\n";
    $trainerRecommendations = \App\Models\Recommendation::with(['child', 'attachments'])
        ->where('trainer_id', $trainerUser->id)
        ->latest()
        ->paginate(10);
    
    echo "✓ Found {$trainerRecommendations->count()} recommendations for trainer\n";
    
    // Test 5: Test the index query for parents
    echo "\n5. Testing parent recommendations query...\n";
    $childIds = $child->user->children->pluck('id');
    $parentRecommendations = \App\Models\Recommendation::with(['trainer', 'child', 'attachments'])
        ->whereIn('child_id', $childIds)
        ->latest()
        ->paginate(10);
    
    echo "✓ Found {$parentRecommendations->count()} recommendations for parent\n";
    
    // Test 6: Test view rendering for trainer
    echo "\n6. Testing view rendering for trainer...\n";
    $view = view('frontend.recommendations.index', [
        'recommendations' => $trainerRecommendations
    ]);
    
    $rendered = $view->render();
    echo "✓ Trainer view rendered successfully (" . strlen($rendered) . " characters)\n";
    
    // Test 7: Test view rendering for parent
    echo "\n7. Testing view rendering for parent...\n";
    $view = view('frontend.recommendations.index', [
        'recommendations' => $parentRecommendations
    ]);
    
    $rendered = $view->render();
    echo "✓ Parent view rendered successfully (" . strlen($rendered) . " characters)\n";
    
    // Clean up test data
    echo "\n8. Cleaning up test data...\n";
    $recommendation->delete();
    echo "✓ Test recommendation deleted\n";
    
    echo "\n✅ All tests passed! The recommendations page should work correctly.\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} 
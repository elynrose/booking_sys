<?php

// Debug file upload process
// Run this on your cloud server

echo "🔍 File Upload Debug\n";
echo "===================\n\n";

// Load Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. Checking Site Settings Model...\n";
echo "=================================\n";

// Check if SiteSettings model exists and has proper file handling
try {
    $siteSettings = \App\Models\SiteSettings::first();
    if ($siteSettings) {
        echo "   ✅ SiteSettings model found\n";
        
        // Check what fields are fillable
        $fillable = $siteSettings->getFillable();
        echo "   📝 Fillable fields: " . implode(', ', $fillable) . "\n";
        
        // Check if logo and banner fields exist
        if (in_array('logo', $fillable)) {
            echo "   ✅ Logo field is fillable\n";
        } else {
            echo "   ❌ Logo field not fillable\n";
        }
        
        if (in_array('banner', $fillable)) {
            echo "   ✅ Banner field is fillable\n";
        } else {
            echo "   ❌ Banner field not fillable\n";
        }
        
        // Check current values
        echo "   📄 Current logo: " . ($siteSettings->logo ?? 'NULL') . "\n";
        echo "   📄 Current banner: " . ($siteSettings->banner ?? 'NULL') . "\n";
        
    } else {
        echo "   ❌ No SiteSettings record found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error accessing SiteSettings: " . $e->getMessage() . "\n";
}

echo "\n2. Checking File Upload Configuration...\n";
echo "=======================================\n";

// Check form request validation
try {
    $requestClass = \App\Http\Requests\SiteSettingsRequest::class;
    if (class_exists($requestClass)) {
        echo "   ✅ SiteSettingsRequest class exists\n";
        
        $reflection = new ReflectionClass($requestClass);
        $methods = $reflection->getMethods();
        
        foreach ($methods as $method) {
            if ($method->getName() === 'rules') {
                $instance = new $requestClass();
                $rules = $instance->rules();
                
                echo "   📋 Validation rules:\n";
                foreach ($rules as $field => $rule) {
                    echo "      $field: $rule\n";
                }
                break;
            }
        }
    } else {
        echo "   ❌ SiteSettingsRequest class not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error checking request class: " . $e->getMessage() . "\n";
}

echo "\n3. Checking Storage Configuration...\n";
echo "===================================\n";

$defaultDisk = config('filesystems.default');
echo "   📁 Default disk: $defaultDisk\n";

$publicDisk = config('filesystems.disks.public');
if ($publicDisk) {
    echo "   📁 Public disk driver: " . ($publicDisk['driver'] ?? 'N/A') . "\n";
    echo "   📁 Public disk root: " . ($publicDisk['root'] ?? 'N/A') . "\n";
    echo "   📁 Public disk URL: " . ($publicDisk['url'] ?? 'N/A') . "\n";
}

echo "\n4. Testing File Upload Process...\n";
echo "=================================\n";

// Simulate file upload process
try {
    $disk = Storage::disk('public');
    $testFile = 'debug-test-' . uniqid() . '.txt';
    $testContent = 'debug-test-' . time();
    
    if ($disk->put($testFile, $testContent)) {
        echo "   ✅ File upload simulation successful\n";
        
        $url = $disk->url($testFile);
        echo "   🔗 Generated URL: $url\n";
        
        // Check if URL is accessible
        $headers = @get_headers($url);
        if ($headers && strpos($headers[0], '200') !== false) {
            echo "   ✅ File accessible via URL\n";
        } else {
            echo "   ❌ File not accessible via URL\n";
            if ($headers) {
                echo "   📋 HTTP Response: " . $headers[0] . "\n";
            }
        }
        
        $disk->delete($testFile);
    } else {
        echo "   ❌ File upload simulation failed\n";
    }
} catch (Exception $e) {
    echo "   ❌ File upload error: " . $e->getMessage() . "\n";
}

echo "\n5. Checking Controller Logic...\n";
echo "==============================\n";

// Check SiteSettingsController
try {
    $controllerClass = \App\Http\Controllers\Admin\SiteSettingsController::class;
    if (class_exists($controllerClass)) {
        echo "   ✅ SiteSettingsController exists\n";
        
        $reflection = new ReflectionClass($controllerClass);
        $methods = $reflection->getMethods();
        
        foreach ($methods as $method) {
            if ($method->getName() === 'update') {
                echo "   ✅ Update method found\n";
                break;
            }
        }
    } else {
        echo "   ❌ SiteSettingsController not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error checking controller: " . $e->getMessage() . "\n";
}

echo "\n6. Checking Database Schema...\n";
echo "=============================\n";

try {
    $schema = \Illuminate\Support\Facades\Schema::getColumnListing('site_settings');
    echo "   📋 Site settings table columns:\n";
    foreach ($schema as $column) {
        echo "      - $column\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error checking schema: " . $e->getMessage() . "\n";
}

echo "\n7. Nginx Upload Analysis...\n";
echo "==========================\n";

echo "   📝 The nginx warning is NORMAL for file uploads:\n";
echo "   - nginx buffers large file uploads to temporary files\n";
echo "   - This prevents memory issues with large uploads\n";
echo "   - The warning doesn't indicate an error\n\n";

echo "   🔍 The real issue might be:\n";
echo "   1. File validation failing\n";
echo "   2. Storage configuration issue\n";
echo "   3. Database update failing\n";
echo "   4. File permissions after upload\n\n";

echo "🔧 TROUBLESHOOTING STEPS:\n";
echo "========================\n";
echo "1. Check Laravel logs: tail -f storage/logs/laravel.log\n";
echo "2. Check nginx error logs: sudo tail -f /var/log/nginx/error.log\n";
echo "3. Try uploading a smaller file first\n";
echo "4. Check if the form has proper enctype=\"multipart/form-data\"\n";
echo "5. Verify the file input names match the controller expectations\n\n";

echo "💡 The nginx buffering is working correctly.\n";
echo "The issue is likely in the Laravel application logic.\n"; 
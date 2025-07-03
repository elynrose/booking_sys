<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Mailgun 401 Error Debug ===\n\n";

// Check current Mailgun configuration
echo "Current Mailgun Configuration:\n";
echo "MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
echo "MAILGUN_DOMAIN: " . env('MAILGUN_DOMAIN') . "\n";
echo "MAILGUN_SECRET: " . (env('MAILGUN_SECRET') ? 'SET (' . substr(env('MAILGUN_SECRET'), 0, 10) . '...)' : 'NOT SET') . "\n";
echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
echo "MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n\n";

// Check services configuration
echo "Services Configuration:\n";
$servicesConfig = config('services.mailgun');
echo "Domain: " . $servicesConfig['domain'] . "\n";
echo "Secret: " . ($servicesConfig['secret'] ? 'SET' : 'NOT SET') . "\n";
echo "Endpoint: " . $servicesConfig['endpoint'] . "\n";
echo "Scheme: " . $servicesConfig['scheme'] . "\n\n";

// Test Mailgun API directly
echo "Testing Mailgun API directly...\n";
try {
    $domain = env('MAILGUN_DOMAIN');
    $secret = env('MAILGUN_SECRET');
    
    if (!$domain || !$secret) {
        echo "❌ Missing domain or secret\n";
    } else {
        // Test API call to Mailgun
        $url = "https://api.mailgun.net/v3/{$domain}/messages";
        $data = [
            'from' => env('MAIL_FROM_ADDRESS'),
            'to' => 'test@example.com',
            'subject' => 'Test Email',
            'text' => 'This is a test email'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, "api:{$secret}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "HTTP Code: {$httpCode}\n";
        echo "Response: " . substr($response, 0, 200) . "\n";
        
        if ($httpCode === 200) {
            echo "✅ Mailgun API test successful\n";
        } elseif ($httpCode === 401) {
            echo "❌ 401 Unauthorized - Check your API key\n";
        } elseif ($httpCode === 403) {
            echo "❌ 403 Forbidden - Check your domain configuration\n";
        } else {
            echo "❌ Unexpected response code: {$httpCode}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ API test error: " . $e->getMessage() . "\n";
}

// Test Laravel mailer
echo "\nTesting Laravel Mailer:\n";
try {
    $mailer = app('mailer');
    echo "✅ Mailer created successfully\n";
    
    // Test sending a simple email
    Mail::raw('Test email from Laravel', function($message) {
        $message->to('test@example.com')
               ->subject('Test Email');
    });
    echo "✅ Email sent successfully\n";
    
} catch (Exception $e) {
    echo "❌ Mailer error: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), '401') !== false) {
        echo "\n🔧 401 Error Solutions:\n";
        echo "1. Check your MAILGUN_SECRET is correct\n";
        echo "2. Verify the API key in your Mailgun dashboard\n";
        echo "3. Make sure you're using the Private API key, not Public\n";
        echo "4. Check if your Mailgun account is active\n";
    }
    
    if (strpos($e->getMessage(), '403') !== false) {
        echo "\n🔧 403 Error Solutions:\n";
        echo "1. Check your MAILGUN_DOMAIN is correct\n";
        echo "2. Verify the domain is properly configured in Mailgun\n";
        echo "3. Check if the domain is verified and active\n";
        echo "4. Ensure your from address matches the domain\n";
    }
}

// Check domain verification
echo "\nDomain Verification Check:\n";
try {
    $domain = env('MAILGUN_DOMAIN');
    $secret = env('MAILGUN_SECRET');
    
    if ($domain && $secret) {
        $url = "https://api.mailgun.net/v3/domains/{$domain}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "api:{$secret}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $domainInfo = json_decode($response, true);
            echo "✅ Domain found: " . $domainInfo['domain']['name'] . "\n";
            echo "Domain state: " . $domainInfo['domain']['state'] . "\n";
            echo "Domain type: " . $domainInfo['domain']['type'] . "\n";
        } else {
            echo "❌ Domain check failed: HTTP {$httpCode}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Domain check error: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";
echo "\n🔧 Common 401 Error Fixes:\n";
echo "1. Use Private API key (not Public)\n";
echo "2. Check domain is verified in Mailgun\n";
echo "3. Ensure from address matches domain\n";
echo "4. Verify Mailgun account is active\n";
echo "5. Check domain DNS records are correct\n"; 
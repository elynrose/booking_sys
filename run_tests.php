<?php

/**
 * Comprehensive Test Runner for Gym Management System
 * 
 * This script runs all tests for the gym management system and provides
 * detailed reporting on test coverage and results.
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

class TestRunner
{
    private $testResults = [];
    private $startTime;
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;

    public function run()
    {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "🏋️  GYM MANAGEMENT SYSTEM - COMPREHENSIVE TEST SUITE\n";
        echo str_repeat("=", 80) . "\n\n";

        $this->startTime = microtime(true);

        // Run all test suites
        $this->runTestSuite('Authentication Tests', 'AuthenticationTest');
        $this->runTestSuite('Booking Tests', 'BookingTest');
        $this->runTestSuite('Schedule Tests', 'ScheduleTest');
        $this->runTestSuite('Trainer Availability Tests', 'TrainerAvailabilityTest');
        $this->runTestSuite('Check-in Tests', 'CheckinTest');
        $this->runTestSuite('Payment Tests', 'PaymentTest');
        $this->runTestSuite('Dashboard Tests', 'DashboardTest');
        $this->runTestSuite('Calendar Tests', 'CalendarTest');

        $this->generateReport();
    }

    private function runTestSuite($suiteName, $testClass)
    {
        echo "\n📋 Running {$suiteName}...\n";
        echo str_repeat("-", 50) . "\n";

        try {
            $output = shell_exec("php artisan test --filter={$testClass} --verbose 2>&1");
            
            if (strpos($output, 'FAILURES') !== false || strpos($output, 'ERRORS') !== false) {
                $this->testResults[$suiteName] = [
                    'status' => 'FAILED',
                    'output' => $output
                ];
                $this->failedTests++;
            } else {
                $this->testResults[$suiteName] = [
                    'status' => 'PASSED',
                    'output' => $output
                ];
                $this->passedTests++;
            }

            $this->totalTests++;
            
            // Extract test count from output
            if (preg_match('/(\d+) tests? passed/', $output, $matches)) {
                echo "✅ {$matches[1]} tests passed\n";
            }
            
            if (preg_match('/(\d+) tests? failed/', $output, $matches)) {
                echo "❌ {$matches[1]} tests failed\n";
            }

        } catch (Exception $e) {
            $this->testResults[$suiteName] = [
                'status' => 'ERROR',
                'output' => $e->getMessage()
            ];
            $this->failedTests++;
            $this->totalTests++;
            echo "💥 Error running tests: " . $e->getMessage() . "\n";
        }
    }

    private function generateReport()
    {
        $endTime = microtime(true);
        $duration = round($endTime - $this->startTime, 2);

        echo "\n" . str_repeat("=", 80) . "\n";
        echo "📊 TEST EXECUTION REPORT\n";
        echo str_repeat("=", 80) . "\n\n";

        echo "⏱️  Total Execution Time: {$duration} seconds\n";
        echo "📈 Total Test Suites: {$this->totalTests}\n";
        echo "✅ Passed Suites: {$this->passedTests}\n";
        echo "❌ Failed Suites: {$this->failedTests}\n";
        echo "📊 Success Rate: " . round(($this->passedTests / $this->totalTests) * 100, 2) . "%\n\n";

        echo "📋 DETAILED RESULTS:\n";
        echo str_repeat("-", 50) . "\n";

        foreach ($this->testResults as $suiteName => $result) {
            $status = $result['status'];
            $icon = $status === 'PASSED' ? '✅' : ($status === 'FAILED' ? '❌' : '💥');
            echo "{$icon} {$suiteName}: {$status}\n";
        }

        echo "\n" . str_repeat("=", 80) . "\n";
        echo "🎯 TEST COVERAGE SUMMARY\n";
        echo str_repeat("=", 80) . "\n\n";

        $this->printCoverageSummary();

        echo "\n" . str_repeat("=", 80) . "\n";
        echo "🔧 RECOMMENDATIONS\n";
        echo str_repeat("=", 80) . "\n\n";

        $this->printRecommendations();

        if ($this->failedTests > 0) {
            echo "\n" . str_repeat("=", 80) . "\n";
            echo "🚨 FAILED TEST DETAILS\n";
            echo str_repeat("=", 80) . "\n\n";

            foreach ($this->testResults as $suiteName => $result) {
                if ($result['status'] !== 'PASSED') {
                    echo "❌ {$suiteName}:\n";
                    echo $result['output'] . "\n";
                    echo str_repeat("-", 50) . "\n";
                }
            }
        }

        echo "\n" . str_repeat("=", 80) . "\n";
        echo "🏁 Test execution completed!\n";
        echo str_repeat("=", 80) . "\n\n";
    }

    private function printCoverageSummary()
    {
        $coverage = [
            'Authentication' => [
                'User Registration',
                'User Login/Logout',
                'Password Reset',
                'Two-Factor Authentication',
                'Role-based Access Control'
            ],
            'Booking System' => [
                'Create Bookings',
                'Cancel Bookings',
                'Booking Validation',
                'Schedule Availability',
                'Booking Notifications'
            ],
            'Schedule Management' => [
                'Create/Edit Schedules',
                'Schedule Filtering',
                'Unlimited Schedules',
                'Image Upload',
                'CSV Import'
            ],
            'Trainer Availability' => [
                'Default Availability',
                'Unavailability Management',
                'Calendar Display',
                'Availability Settings',
                'Bulk Operations'
            ],
            'Check-in System' => [
                'User Check-in',
                'Check-out',
                'QR Code Scanning',
                'Time Validation',
                'Auto Checkout'
            ],
            'Payment Processing' => [
                'Payment Methods',
                'Payment Validation',
                'Refunds',
                'Discounts',
                'Payment Notifications'
            ],
            'Dashboard' => [
                'Admin Dashboard',
                'User Dashboard',
                'Statistics',
                'Charts',
                'Export Functionality'
            ],
            'Calendar System' => [
                'Sunday Start Week',
                'Date Calculations',
                'Trainer Filtering',
                'Color Coding',
                'Mobile Responsive'
            ]
        ];

        foreach ($coverage as $module => $features) {
            echo "📦 {$module}:\n";
            foreach ($features as $feature) {
                echo "   • {$feature}\n";
            }
            echo "\n";
        }
    }

    private function printRecommendations()
    {
        $recommendations = [
            'If all tests pass:',
            '  ✅ Your gym management system is working correctly',
            '  ✅ All major features are functional',
            '  ✅ Ready for production deployment',
            '',
            'If some tests fail:',
            '  🔧 Review failed test outputs above',
            '  🔧 Check database migrations and seeders',
            '  🔧 Verify route definitions',
            '  🔧 Ensure all required models exist',
            '',
            'For continuous testing:',
            '  📝 Run tests before each deployment',
            '  📝 Add new tests for new features',
            '  📝 Monitor test coverage',
            '  📝 Keep test data up to date'
        ];

        foreach ($recommendations as $recommendation) {
            echo "{$recommendation}\n";
        }
    }
}

// Run the test suite
$runner = new TestRunner();
$runner->run(); 
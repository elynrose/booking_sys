#!/bin/bash

# Comprehensive Test Runner for Gym Management System
# This script runs all tests and provides detailed reporting

echo "🏋️  GYM MANAGEMENT SYSTEM - TEST SUITE"
echo "========================================"
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Laravel project root directory"
    exit 1
fi

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo "📦 Installing dependencies..."
    composer install
fi

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Set up testing environment
echo "🔧 Setting up testing environment..."
php artisan config:cache
php artisan route:cache

# Run all tests
echo ""
echo "🧪 Running comprehensive test suite..."
echo ""

# Run the test runner
php run_tests.php

echo ""
echo "✅ Test execution completed!"
echo ""
echo "📊 To run individual test suites:"
echo "   php artisan test --filter=AuthenticationTest"
echo "   php artisan test --filter=BookingTest"
echo "   php artisan test --filter=ScheduleTest"
echo "   php artisan test --filter=TrainerAvailabilityTest"
echo "   php artisan test --filter=CheckinTest"
echo "   php artisan test --filter=PaymentTest"
echo "   php artisan test --filter=DashboardTest"
echo "   php artisan test --filter=CalendarTest"
echo ""
echo "📊 To run all tests with coverage:"
echo "   php artisan test --coverage"
echo "" 
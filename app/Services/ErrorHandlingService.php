<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ErrorHandlingService
{
    // Error Categories
    const CATEGORY_AUTHENTICATION = 'AUTH';
    const CATEGORY_AUTHORIZATION = 'AUTHZ';
    const CATEGORY_VALIDATION = 'VALID';
    const CATEGORY_BUSINESS_LOGIC = 'BUSINESS';
    const CATEGORY_SYSTEM = 'SYSTEM';
    const CATEGORY_EXTERNAL = 'EXTERNAL';

    // Error Codes
    const ERROR_CODES = [
        // Authentication Errors (1000-1999)
        'AUTH_001' => 'Member ID not found',
        'AUTH_002' => 'Member ID verification failed',
        'AUTH_003' => 'Session expired',
        'AUTH_004' => 'Invalid credentials',
        'AUTH_005' => 'Account locked',
        'AUTH_006' => 'Email not verified',
        'AUTH_007' => 'Two-factor authentication required',
        'AUTH_008' => 'Rate limit exceeded',

        // Authorization Errors (2000-2999)
        'AUTHZ_001' => 'Insufficient permissions',
        'AUTHZ_002' => 'Resource ownership mismatch',
        'AUTHZ_003' => 'Role-based access denied',
        'AUTHZ_004' => 'Admin access required',
        'AUTHZ_005' => 'Trainer access required',

        // Validation Errors (3000-3999)
        'VALID_001' => 'Required field missing',
        'VALID_002' => 'Invalid data format',
        'VALID_003' => 'Data out of range',
        'VALID_004' => 'Duplicate entry',
        'VALID_005' => 'Invalid date/time',
        'VALID_006' => 'File upload failed',
        'VALID_007' => 'Invalid file type',
        'VALID_008' => 'File size exceeded',

        // Business Logic Errors (4000-4999)
        'BUSINESS_001' => 'Schedule not available',
        'BUSINESS_002' => 'Booking already exists',
        'BUSINESS_003' => 'No sessions remaining',
        'BUSINESS_004' => 'Trainer not available',
        'BUSINESS_005' => 'Class is full',
        'BUSINESS_006' => 'Class has ended',
        'BUSINESS_007' => 'Check-in already completed',
        'BUSINESS_008' => 'Check-out already completed',
        'BUSINESS_009' => 'Payment required',
        'BUSINESS_010' => 'Invalid booking status',
        'BUSINESS_011' => 'Future check-in not allowed',
        'BUSINESS_012' => 'Late check-in limit exceeded',
        'BUSINESS_013' => 'Child not found',
        'BUSINESS_014' => 'Category not found',
        'BUSINESS_015' => 'Price calculation error',

        // System Errors (5000-5999)
        'SYSTEM_001' => 'Database connection failed',
        'SYSTEM_002' => 'File system error',
        'SYSTEM_003' => 'Cache error',
        'SYSTEM_004' => 'Queue processing failed',
        'SYSTEM_005' => 'Email sending failed',
        'SYSTEM_006' => 'SMS sending failed',
        'SYSTEM_007' => 'Payment gateway error',
        'SYSTEM_008' => 'Storage service error',

        // External Service Errors (6000-6999)
        'EXTERNAL_001' => 'Payment gateway timeout',
        'EXTERNAL_002' => 'SMS service unavailable',
        'EXTERNAL_003' => 'Email service down',
        'EXTERNAL_004' => 'Third-party API error',
        'EXTERNAL_005' => 'Cloud storage error',
    ];

    /**
     * Create a detailed error response
     */
    public function createError(
        string $errorCode,
        string $userMessage,
        array $context = [],
        ?string $category = null,
        int $httpCode = 400
    ): array {
        $category = $category ?: $this->getCategoryFromCode($errorCode);
        
        $error = [
            'code' => $errorCode,
            'category' => $category,
            'message' => $userMessage,
            'timestamp' => now()->toISOString(),
            'reference_id' => $this->generateReferenceId(),
            'context' => $context,
            'http_code' => $httpCode,
        ];

        // Log the error with full context
        $this->logError($error);

        return $error;
    }

    /**
     * Handle booking-related errors
     */
    public function handleBookingError(string $errorCode, array $context = []): array
    {
        $messages = [
            'BUSINESS_001' => 'This class is not available for booking at this time.',
            'BUSINESS_002' => 'You already have an active booking with sessions remaining for this class.',
            'BUSINESS_003' => 'You have no sessions remaining for this booking.',
            'BUSINESS_004' => 'The trainer is not available for the selected time.',
            'BUSINESS_005' => 'This class is currently full.',
            'BUSINESS_006' => 'This class has already ended.',
            'BUSINESS_009' => 'Payment is required to complete this booking.',
            'BUSINESS_010' => 'This booking cannot be modified in its current status.',
        ];

        $userMessage = $messages[$errorCode] ?? 'An error occurred while processing your booking.';
        
        return $this->createError($errorCode, $userMessage, $context, self::CATEGORY_BUSINESS_LOGIC);
    }

    /**
     * Handle check-in related errors
     */
    public function handleCheckinError(string $errorCode, array $context = []): array
    {
        $messages = [
            'AUTH_001' => 'Member ID not found. Please verify your member ID.',
            'AUTH_002' => 'Member verification failed. Please try again.',
            'BUSINESS_007' => 'You have already checked in for this class today.',
            'BUSINESS_008' => 'You have already checked out for this class today.',
            'BUSINESS_011' => 'Check-in is not available for future classes.',
            'BUSINESS_012' => 'Check-in is not available for classes that have ended.',
            'BUSINESS_013' => 'No check-in record found for today.',
        ];

        $userMessage = $messages[$errorCode] ?? 'An error occurred during check-in.';
        
        return $this->createError($errorCode, $userMessage, $context, self::CATEGORY_BUSINESS_LOGIC);
    }

    /**
     * Handle payment-related errors
     */
    public function handlePaymentError(string $errorCode, array $context = []): array
    {
        $messages = [
            'BUSINESS_009' => 'Payment is required to complete this transaction.',
            'SYSTEM_007' => 'Payment processing failed. Please try again.',
            'EXTERNAL_001' => 'Payment gateway is temporarily unavailable.',
            'EXTERNAL_002' => 'Payment processing timeout. Please try again.',
        ];

        $userMessage = $messages[$errorCode] ?? 'An error occurred during payment processing.';
        
        return $this->createError($errorCode, $userMessage, $context, self::CATEGORY_BUSINESS_LOGIC);
    }

    /**
     * Handle validation errors
     */
    public function handleValidationError(string $field, string $rule, array $context = []): array
    {
        $errorCode = 'VALID_001';
        $userMessage = "The {$field} field is invalid.";
        
        // Map common validation rules to specific error codes
        $ruleMap = [
            'required' => ['VALID_001', 'This field is required.'],
            'email' => ['VALID_002', 'Please enter a valid email address.'],
            'numeric' => ['VALID_002', 'Please enter a valid number.'],
            'date' => ['VALID_005', 'Please enter a valid date.'],
            'exists' => ['VALID_004', 'The selected item does not exist.'],
            'unique' => ['VALID_004', 'This value is already taken.'],
            'min' => ['VALID_003', 'The value is too small.'],
            'max' => ['VALID_003', 'The value is too large.'],
        ];

        if (isset($ruleMap[$rule])) {
            $errorCode = $ruleMap[$rule][0];
            $userMessage = $ruleMap[$rule][1];
        }

        $context['field'] = $field;
        $context['rule'] = $rule;
        
        return $this->createError($errorCode, $userMessage, $context, self::CATEGORY_VALIDATION);
    }

    /**
     * Handle system errors
     */
    public function handleSystemError(\Exception $exception, array $context = []): array
    {
        $errorCode = 'SYSTEM_001';
        $userMessage = 'A system error occurred. Please try again later.';
        
        // Map exception types to specific error codes
        if ($exception instanceof \Illuminate\Database\QueryException) {
            $errorCode = 'SYSTEM_001';
            $userMessage = 'Database error occurred. Please try again.';
        } elseif ($exception instanceof \Illuminate\Filesystem\FileNotFoundException) {
            $errorCode = 'SYSTEM_002';
            $userMessage = 'File system error occurred.';
        } elseif ($exception instanceof \Illuminate\Cache\CacheException) {
            $errorCode = 'SYSTEM_003';
            $userMessage = 'Cache error occurred.';
        }

        $context['exception'] = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];

        return $this->createError($errorCode, $userMessage, $context, self::CATEGORY_SYSTEM, 500);
    }

    /**
     * Get detailed error information for staff
     */
    public function getStaffErrorDetails(array $error): array
    {
        $details = [
            'error_code' => $error['code'],
            'category' => $error['category'],
            'user_message' => $error['message'],
            'technical_details' => [
                'timestamp' => $error['timestamp'],
                'reference_id' => $error['reference_id'],
                'http_code' => $error['http_code'],
                'context' => $error['context'],
            ],
            'troubleshooting' => $this->getTroubleshootingSteps($error['code']),
            'resolution_steps' => $this->getResolutionSteps($error['code']),
        ];

        return $details;
    }

    /**
     * Get troubleshooting steps for staff
     */
    private function getTroubleshootingSteps(string $errorCode): array
    {
        $steps = [
            'AUTH_001' => [
                'Verify member ID exists in database',
                'Check if member ID format is correct',
                'Confirm member account is active',
            ],
            'BUSINESS_001' => [
                'Check schedule status in admin panel',
                'Verify schedule dates and times',
                'Check if schedule is active',
                'Confirm maximum participants limit',
            ],
            'BUSINESS_004' => [
                'Check trainer availability calendar',
                'Verify trainer unavailability settings',
                'Check trainer working hours',
                'Confirm trainer is assigned to schedule',
            ],
            'SYSTEM_001' => [
                'Check database connection',
                'Verify database credentials',
                'Check database server status',
                'Review database logs',
            ],
            'EXTERNAL_001' => [
                'Check payment gateway status',
                'Verify API credentials',
                'Check network connectivity',
                'Review payment gateway logs',
            ],
        ];

        return $steps[$errorCode] ?? ['Review system logs for additional details'];
    }

    /**
     * Get resolution steps for staff
     */
    private function getResolutionSteps(string $errorCode): array
    {
        $steps = [
            'AUTH_001' => [
                'Create new member account if needed',
                'Update member ID format',
                'Activate member account',
            ],
            'BUSINESS_001' => [
                'Activate schedule in admin panel',
                'Update schedule dates if needed',
                'Increase maximum participants if needed',
            ],
            'BUSINESS_004' => [
                'Update trainer availability',
                'Remove unavailability blocks',
                'Adjust trainer working hours',
                'Reassign trainer if needed',
            ],
            'SYSTEM_001' => [
                'Restart database service',
                'Update database credentials',
                'Contact hosting provider',
                'Restore from backup if needed',
            ],
            'EXTERNAL_001' => [
                'Wait for payment gateway to recover',
                'Update API credentials',
                'Contact payment provider support',
                'Switch to backup payment method',
            ],
        ];

        return $steps[$errorCode] ?? ['Contact system administrator'];
    }

    /**
     * Get category from error code
     */
    private function getCategoryFromCode(string $errorCode): string
    {
        $prefix = substr($errorCode, 0, 4);
        
        $categories = [
            'AUTH' => self::CATEGORY_AUTHENTICATION,
            'AUTHZ' => self::CATEGORY_AUTHORIZATION,
            'VALID' => self::CATEGORY_VALIDATION,
            'BUSINESS' => self::CATEGORY_BUSINESS_LOGIC,
            'SYSTEM' => self::CATEGORY_SYSTEM,
            'EXTERNAL' => self::CATEGORY_EXTERNAL,
        ];

        return $categories[$prefix] ?? self::CATEGORY_SYSTEM;
    }

    /**
     * Generate unique reference ID
     */
    private function generateReferenceId(): string
    {
        return 'ERR-' . strtoupper(uniqid()) . '-' . time();
    }

    /**
     * Log error with full context
     */
    private function logError(array $error): void
    {
        $logData = [
            'error_code' => $error['code'],
            'category' => $error['category'],
            'message' => $error['message'],
            'reference_id' => $error['reference_id'],
            'context' => $error['context'],
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->url(),
            'method' => request()->method(),
        ];

        Log::error("Application Error: {$error['code']}", $logData);
    }

    /**
     * Store error in session for display
     */
    public function storeErrorInSession(array $error): void
    {
        Session::flash('error', $error['message']);
        Session::flash('error_code', $error['code']);
        Session::flash('error_reference', $error['reference_id']);
        
        // Store detailed error for staff view
        if (auth()->check() && auth()->user()->hasRole('Admin')) {
            Session::flash('error_details', $this->getStaffErrorDetails($error));
        }
    }

    /**
     * Get all error codes for reference
     */
    public function getAllErrorCodes(): array
    {
        return self::ERROR_CODES;
    }
} 
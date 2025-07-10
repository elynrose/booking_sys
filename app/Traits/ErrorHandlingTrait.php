<?php

namespace App\Traits;

use App\Services\ErrorHandlingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait ErrorHandlingTrait
{
    protected ErrorHandlingService $errorHandler;

    public function __construct()
    {
        $this->errorHandler = app(ErrorHandlingService::class);
    }

    /**
     * Handle booking errors with detailed error codes
     */
    protected function handleBookingError(string $errorCode, array $context = []): RedirectResponse
    {
        $error = $this->errorHandler->handleBookingError($errorCode, $context);
        $this->errorHandler->storeErrorInSession($error);
        
        return redirect()->back()->withInput();
    }

    /**
     * Handle check-in errors with detailed error codes
     */
    protected function handleCheckinError(string $errorCode, array $context = []): RedirectResponse
    {
        $error = $this->errorHandler->handleCheckinError($errorCode, $context);
        $this->errorHandler->storeErrorInSession($error);
        // Store detailed error list if present
        if (isset($context['errors']) && is_array($context['errors']) && count($context['errors']) > 0) {
            session()->flash('error_list', $context['errors']);
        }
        return redirect()->back()->withInput();
    }

    /**
     * Handle payment errors with detailed error codes
     */
    protected function handlePaymentError(string $errorCode, array $context = []): RedirectResponse
    {
        $error = $this->errorHandler->handlePaymentError($errorCode, $context);
        $this->errorHandler->storeErrorInSession($error);
        
        return redirect()->back()->withInput();
    }

    /**
     * Handle validation errors with detailed error codes
     */
    protected function handleValidationError(string $field, string $rule, array $context = []): RedirectResponse
    {
        $error = $this->errorHandler->handleValidationError($field, $rule, $context);
        $this->errorHandler->storeErrorInSession($error);
        
        return redirect()->back()->withInput();
    }

    /**
     * Handle system errors with detailed error codes
     */
    protected function handleSystemError(\Exception $exception, array $context = []): RedirectResponse
    {
        $error = $this->errorHandler->handleSystemError($exception, $context);
        $this->errorHandler->storeErrorInSession($error);
        
        return redirect()->back()->withInput();
    }

    /**
     * Handle authentication errors
     */
    protected function handleAuthError(string $errorCode, array $context = []): RedirectResponse
    {
        $error = $this->errorHandler->createError($errorCode, 'Authentication failed', $context, ErrorHandlingService::CATEGORY_AUTHENTICATION);
        $this->errorHandler->storeErrorInSession($error);
        
        return redirect()->back()->withInput();
    }

    /**
     * Handle authorization errors
     */
    protected function handleAuthzError(string $errorCode, array $context = []): RedirectResponse
    {
        $error = $this->errorHandler->createError($errorCode, 'Access denied', $context, ErrorHandlingService::CATEGORY_AUTHORIZATION);
        $this->errorHandler->storeErrorInSession($error);
        
        return redirect()->back()->withInput();
    }

    /**
     * Return JSON error response for API endpoints
     */
    protected function jsonError(string $errorCode, string $message, array $context = [], int $httpCode = 400): JsonResponse
    {
        $error = $this->errorHandler->createError($errorCode, $message, $context);
        
        return response()->json([
            'error' => $error,
            'success' => false
        ], $httpCode);
    }

    /**
     * Log error with context for debugging
     */
    protected function logError(string $errorCode, string $message, array $context = []): void
    {
        Log::error("Controller Error: {$errorCode}", [
            'message' => $message,
            'context' => $context,
            'user_id' => auth()->id(),
            'url' => request()->url(),
            'method' => request()->method(),
        ]);
    }

    /**
     * Check if user has required permissions
     */
    protected function checkPermission(string $permission, array $context = []): bool
    {
        if (!auth()->check()) {
            $this->handleAuthError('AUTH_003', $context);
            return false;
        }

        if (!auth()->user()->can($permission)) {
            $this->handleAuthzError('AUTHZ_001', array_merge($context, ['permission' => $permission]));
            return false;
        }

        return true;
    }

    /**
     * Check if user owns the resource
     */
    protected function checkOwnership($resource, array $context = []): bool
    {
        if (!$resource) {
            $this->handleAuthzError('AUTHZ_002', $context);
            return false;
        }

        $userId = $resource->user_id ?? $resource->id ?? null;
        
        if ($userId && $userId !== auth()->id() && !auth()->user()->hasRole('Admin')) {
            $this->handleAuthzError('AUTHZ_002', array_merge($context, [
                'resource_id' => $resource->id ?? 'unknown',
                'resource_type' => get_class($resource)
            ]));
            return false;
        }

        return true;
    }

    /**
     * Validate business rules
     */
    protected function validateBusinessRule(bool $condition, string $errorCode, string $message, array $context = []): bool
    {
        if (!$condition) {
            $this->handleBookingError($errorCode, array_merge($context, ['validation_message' => $message]));
            return false;
        }

        return true;
    }

    /**
     * Handle database transaction errors
     */
    protected function handleTransactionError(\Exception $exception, array $context = []): RedirectResponse
    {
        $errorContext = array_merge($context, [
            'operation' => 'database_transaction',
            'exception_type' => get_class($exception),
        ]);

        return $this->handleSystemError($exception, $errorContext);
    }

    /**
     * Handle external service errors
     */
    protected function handleExternalServiceError(string $service, \Exception $exception, array $context = []): RedirectResponse
    {
        $errorCode = 'EXTERNAL_004';
        $userMessage = "Service temporarily unavailable. Please try again later.";
        
        $error = $this->errorHandler->createError($errorCode, $userMessage, array_merge($context, [
            'service' => $service,
            'exception' => [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]
        ]), ErrorHandlingService::CATEGORY_EXTERNAL);
        
        $this->errorHandler->storeErrorInSession($error);
        
        return redirect()->back()->withInput();
    }

    /**
     * Get error details for staff view
     */
    protected function getErrorDetails(string $errorCode): array
    {
        return $this->errorHandler->getStaffErrorDetails([
            'code' => $errorCode,
            'category' => $this->errorHandler->getCategoryFromCode($errorCode),
            'message' => 'Error occurred',
            'timestamp' => now()->toISOString(),
            'reference_id' => $this->errorHandler->generateReferenceId(),
            'context' => [],
            'http_code' => 400,
        ]);
    }
} 
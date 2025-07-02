<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GymAppException extends Exception
{
    protected $context = [];
    protected $userMessage = null;

    public function __construct(string $message = "", int $code = 0, ?Exception $previous = null, array $context = [], ?string $userMessage = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
        $this->userMessage = $userMessage ?? $message;
    }

    /**
     * Get the context data for logging
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get user-friendly message
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * Report the exception
     */
    public function report(): void
    {
        \Log::error($this->getMessage(), [
            'exception' => $this,
            'context' => $this->context,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
        ]);
    }

    /**
     * Render the exception into an HTTP response
     */
    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $this->getUserMessage(),
                'code' => $this->getCode(),
            ], $this->getCode() ?: 500);
        }

        return response()->view('errors.gym-app', [
            'message' => $this->getUserMessage(),
            'code' => $this->getCode() ?: 500,
        ], $this->getCode() ?: 500);
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'booking_id' => [
                'required',
                'integer',
                'exists:bookings,id',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99',
            ],
            'payment_method' => [
                'required',
                'string',
                'in:stripe,cash,card,transfer',
            ],
            'transaction_id' => [
                'nullable',
                'string',
                'max:255',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];

        // Add custom validation for booking ownership (unless admin)
        if (!auth()->user()->hasRole('Admin')) {
            $rules['booking_id'] = [
                'required',
                'integer',
                'exists:bookings,id',
                Rule::exists('bookings', 'id')->where(function ($query) {
                    $query->where('user_id', auth()->id());
                }),
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'booking_id.required' => 'Please select a booking for payment.',
            'booking_id.exists' => 'The selected booking does not exist.',
            'amount.required' => 'Payment amount is required.',
            'amount.numeric' => 'Payment amount must be a valid number.',
            'amount.min' => 'Payment amount must be at least $0.01.',
            'amount.max' => 'Payment amount cannot exceed $9,999.99.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Please select a valid payment method.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'booking_id' => 'booking',
            'amount' => 'payment amount',
            'payment_method' => 'payment method',
            'transaction_id' => 'transaction ID',
            'notes' => 'notes',
        ];
    }
}

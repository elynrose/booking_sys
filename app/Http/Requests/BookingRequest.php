<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
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
            'schedule_id' => [
                'required',
                'integer',
                'exists:schedules,id',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'children' => [
                'nullable',
                'array',
            ],
            'children.*' => [
                'integer',
                'exists:children,id',
            ],
        ];

        // Add custom validation for children ownership
        if ($this->has('children')) {
            $rules['children.*'] = [
                'integer',
                'exists:children,id',
                Rule::exists('children', 'id')->where(function ($query) {
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
            'schedule_id.required' => 'Please select a class to book.',
            'schedule_id.exists' => 'The selected class does not exist.',
            'children.*.exists' => 'One or more selected children do not belong to you.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'schedule_id' => 'class',
            'children' => 'children',
            'notes' => 'notes',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure children is always an array
        if ($this->has('children') && !is_array($this->children)) {
            $this->merge([
                'children' => [$this->children]
            ]);
        }
    }
}

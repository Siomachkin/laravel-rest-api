<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

abstract class BaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();
        
        // Sanitize string inputs
        $sanitized = $this->sanitizeInput($input);
        
        $this->replace($sanitized);
    }

    private function sanitizeInput(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $input[$key] = $this->sanitizeInput($value);
            }
        }
        
        return $input;
    }

    private function sanitizeString(string $value): string
    {
        // Remove HTML tags except for specific allowed ones
        $value = strip_tags($value);
        
        // Trim whitespace
        $value = trim($value);
        
        // Remove null bytes
        $value = str_replace("\0", '', $value);
        
        return $value;
    }

    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a string.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'min' => 'The :attribute must be at least :min characters.',
            'email' => 'The :attribute must be a valid email address.',
            'unique' => 'The :attribute has already been taken.',
            'boolean' => 'The :attribute field must be true or false.',
            'array' => 'The :attribute must be an array.',
            'regex' => 'The :attribute format is invalid.',
        ];
    }
}
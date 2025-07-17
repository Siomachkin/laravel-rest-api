<?php

namespace App\Http\Requests\Api\V1;

class StoreUserRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255', 'min:1'],
            'last_name' => ['required', 'string', 'max:255', 'min:1'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\+\d\s\-\(\)]+$/'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'emails' => ['required', 'array', 'min:1', 'max:20'],
            'emails.*.email' => ['required', 'email', 'max:255', 'unique:user_emails,email'],
            'emails.*.is_primary' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'emails.required' => 'At least one email address is required',
            'emails.*.email.required' => 'Email address is required',
            'emails.*.email.email' => 'Please provide a valid email address',
            'emails.*.email.unique' => 'This email address is already taken',
        ];
    }

    /**
     * Define body parameters for Scribe documentation
     */
    public function bodyParameters(): array
    {
        return [
            'first_name' => [
                'description' => 'The user\'s first name',
                'example' => 'John',
            ],
            'last_name' => [
                'description' => 'The user\'s last name',
                'example' => 'Doe',
            ],
            'phone' => [
                'description' => 'The user\'s phone number',
                'example' => '+1234567890',
            ],
            'password' => [
                'description' => 'The user\'s password (minimum 8 characters)',
                'example' => 'password123',
            ],
            'emails' => [
                'description' => 'Array of email objects (at least one required)',
                'example' => [
                    [
                        'email' => 'john@example.com',
                        'is_primary' => true
                    ],
                    [
                        'email' => 'john.work@example.com',
                        'is_primary' => false
                    ]
                ],
            ],
            'emails.*.email' => [
                'description' => 'Email address',
                'example' => 'john@example.com',
            ],
            'emails.*.is_primary' => [
                'description' => 'Whether this email should be primary',
                'example' => true,
            ],
        ];
    }
}

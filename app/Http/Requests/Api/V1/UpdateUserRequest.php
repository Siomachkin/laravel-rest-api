<?php

namespace App\Http\Requests\Api\V1;

class UpdateUserRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        
        return [
            'first_name' => ['sometimes', 'string', 'max:255', 'min:1'],
            'last_name' => ['sometimes', 'string', 'max:255', 'min:1'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^[\+\d\s\-\(\)]+$/'],
            'password' => ['sometimes', 'string', 'min:8', 'max:255'],
            'emails' => ['sometimes', 'array', 'min:1', 'max:20'],
            'emails.*.email' => [
                'required_with:emails', 
                'email', 
                'max:255',
                function ($attribute, $value, $fail) use ($user) {
                    $existingEmail = \App\Models\UserEmail::where('email', $value)
                        ->where('user_id', '!=', $user->id)
                        ->first();
                    
                    if ($existingEmail) {
                        $fail('This email address is already taken.');
                    }
                }
            ],
            'emails.*.is_primary' => ['sometimes', 'boolean'],
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
                'description' => 'New password (minimum 8 characters)',
                'example' => 'newpassword123',
            ],
            'emails' => [
                'description' => 'Array of email objects to replace existing ones',
                'example' => [
                    [
                        'email' => 'john.new@example.com',
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
                'example' => 'john.new@example.com',
            ],
            'emails.*.is_primary' => [
                'description' => 'Whether this email should be primary',
                'example' => true,
            ],
        ];
    }
}

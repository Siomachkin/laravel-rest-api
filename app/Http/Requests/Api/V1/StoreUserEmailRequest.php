<?php

namespace App\Http\Requests\Api\V1;

class StoreUserEmailRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', 'unique:user_emails,email'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email address is already taken',
        ];
    }

    /**
     * Define body parameters for Scribe documentation
     */
    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'The email address',
                'example' => 'john.new@example.com',
            ],
            'is_primary' => [
                'description' => 'Whether this email should be set as primary',
                'example' => true,
            ],
        ];
    }
}

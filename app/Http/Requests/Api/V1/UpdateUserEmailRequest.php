<?php

namespace App\Http\Requests\Api\V1;

class UpdateUserEmailRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $emailId = $this->route('email')->id ?? null;

        return [
            'email' => ['sometimes', 'email', 'max:255', 'unique:user_emails,email,' . $emailId],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Define body parameters for Scribe documentation
     */
    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'The new email address',
                'example' => 'john.updated@example.com',
            ],
            'is_primary' => [
                'description' => 'Whether this email should be set as primary',
                'example' => true,
            ],
        ];
    }
}

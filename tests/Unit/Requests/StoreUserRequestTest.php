<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Models\UserEmail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class StoreUserRequestTest extends TestCase
{
    use RefreshDatabase;

    private function getValidData(): array
    {
        return [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+1234567890',
            'password' => 'password123',
            'emails' => [
                [
                    'email' => 'john@example.com',
                    'is_primary' => true
                ]
            ]
        ];
    }

    public function test_valid_data_passes(): void
    {
        $request = new StoreUserRequest();
        $validator = Validator::make($this->getValidData(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_first_name_is_required(): void
    {
        $data = $this->getValidData();
        unset($data['first_name']);

        $request = new StoreUserRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('first_name', $validator->errors()->toArray());
    }

    public function test_last_name_is_required(): void
    {
        $data = $this->getValidData();
        unset($data['last_name']);

        $request = new StoreUserRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('last_name', $validator->errors()->toArray());
    }

    public function test_password_minimum_length(): void
    {
        $data = $this->getValidData();
        $data['password'] = '123';

        $request = new StoreUserRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_emails_are_required(): void
    {
        $data = $this->getValidData();
        unset($data['emails']);

        $request = new StoreUserRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('emails', $validator->errors()->toArray());
    }

    public function test_invalid_email_format(): void
    {
        $data = $this->getValidData();
        $data['emails'][0]['email'] = 'invalid-email';

        $request = new StoreUserRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('emails.0.email', $validator->errors()->toArray());
    }

    public function test_duplicate_email_fails(): void
    {
        UserEmail::factory()->create(['email' => 'existing@example.com']);

        $data = $this->getValidData();
        $data['emails'][0]['email'] = 'existing@example.com';

        $request = new StoreUserRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('emails.0.email', $validator->errors()->toArray());
    }

    public function test_phone_format_validation(): void
    {
        $data = $this->getValidData();
        $data['phone'] = 'invalid-phone';

        $request = new StoreUserRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_phone_is_nullable(): void
    {
        $data = $this->getValidData();
        $data['phone'] = null;

        $request = new StoreUserRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_max_emails_limit(): void
    {
        $data = $this->getValidData();
        $data['emails'] = [];
        
        for ($i = 0; $i < 21; $i++) {
            $data['emails'][] = [
                'email' => "email{$i}@example.com",
                'is_primary' => $i === 0
            ];
        }

        $request = new StoreUserRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('emails', $validator->errors()->toArray());
    }
}
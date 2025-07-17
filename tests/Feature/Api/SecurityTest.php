<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserEmail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_is_hashed_on_creation(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'plaintext-password',
            'emails' => [
                [
                    'email' => 'john@example.com',
                    'is_primary' => true
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/users', $userData);
        $response->assertStatus(201);

        $user = User::first();
        $this->assertTrue(Hash::check('plaintext-password', $user->password));
        $this->assertNotEquals('plaintext-password', $user->password);
    }

    public function test_password_is_not_returned_in_api_response(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$user->id}");
        
        $response->assertStatus(200);
        $response->assertJsonMissing(['password']);
        $this->assertArrayNotHasKey('password', $response->json('data'));
    }

    public function test_sql_injection_prevention_in_search(): void
    {
        User::factory()->create(['first_name' => 'John']);
        
        $maliciousSearch = "'; DROP TABLE users; --";
        
        $response = $this->getJson("/api/v1/users?search=" . urlencode($maliciousSearch));
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['first_name' => 'John']);
    }

    public function test_email_uniqueness_prevents_duplicate_registration(): void
    {
        UserEmail::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'password123',
            'emails' => [
                [
                    'email' => 'existing@example.com',
                    'is_primary' => true
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/users', $userData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['emails.0.email']);
    }

    public function test_xss_prevention_in_user_data(): void
    {
        $userData = [
            'first_name' => '<script>alert("xss")</script>',
            'last_name' => 'Test',
            'password' => 'password123',
            'emails' => [
                [
                    'email' => 'test@example.com',
                    'is_primary' => true
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/users', $userData);
        
        $response->assertStatus(201);
        
        $user = User::first();
        $this->assertStringContainsString('alert', $user->first_name);
        $this->assertEquals('Test', $user->last_name);
        
        $apiResponse = $this->getJson("/api/v1/users/{$user->id}");
        $apiResponse->assertStatus(200);
        
        $responseData = $apiResponse->json('data');
        $this->assertStringContainsString('alert', $responseData['first_name']);
    }

    public function test_mass_assignment_protection(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'password123',
            'id' => 999999,
            'created_at' => '2020-01-01',
            'updated_at' => '2020-01-01',
            'emails' => [
                [
                    'email' => 'john@example.com',
                    'is_primary' => true
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/users', $userData);
        
        $response->assertStatus(201);
        
        $user = User::first();
        $this->assertNotEquals(999999, $user->id);
        $this->assertNotEquals('2020-01-01', $user->created_at->format('Y-m-d'));
    }

    public function test_input_length_limits(): void
    {
        $userData = [
            'first_name' => str_repeat('a', 256),
            'last_name' => str_repeat('b', 256),
            'phone' => str_repeat('1', 21),
            'password' => 'password123',
            'emails' => [
                [
                    'email' => 'test@example.com',
                    'is_primary' => true
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/users', $userData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name', 'last_name', 'phone']);
    }

    public function test_email_format_validation(): void
    {
        $invalidEmails = [
            'invalid-email',
            'test@',
            '@example.com'
        ];

        foreach ($invalidEmails as $email) {
            $userData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password' => 'password123',
                'emails' => [
                    [
                        'email' => $email,
                        'is_primary' => true
                    ]
                ]
            ];

            $response = $this->postJson('/api/v1/users', $userData);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['emails.0.email']);
        }
    }

    public function test_phone_format_validation(): void
    {
        $invalidPhones = [
            'abc123',
            '123abc456',
            'phone-number'
        ];

        foreach ($invalidPhones as $phone) {
            $userData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => $phone,
                'password' => 'password123',
                'emails' => [
                    [
                        'email' => 'test@example.com',
                        'is_primary' => true
                    ]
                ]
            ];

            $response = $this->postJson('/api/v1/users', $userData);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['phone']);
        }
    }
}
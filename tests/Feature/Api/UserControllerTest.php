<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserEmail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_users_list(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'full_name',
                        'phone',
                        'primary_email',
                        'emails',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ]
            ]);
    }

    public function test_can_search_users(): void
    {
        User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        User::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

        $response = $this->getJson('/api/v1/users?search=John');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('pagination.total'));
        $this->assertStringContainsString('John', $response->json('data.0.first_name'));
    }

    public function test_can_create_user_with_emails(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+1234567890',
            'password' => 'password123',
            'emails' => [
                ['email' => 'john@example.com', 'is_primary' => true],
                ['email' => 'john.work@example.com', 'is_primary' => false]
            ]
        ];

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'full_name',
                    'phone',
                    'primary_email',
                    'emails' => [
                        '*' => [
                            'id',
                            'email',
                            'is_primary',
                            'verified_at',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+1234567890'
        ]);

        $this->assertDatabaseHas('user_emails', [
            'email' => 'john@example.com',
            'is_primary' => true
        ]);

        $this->assertDatabaseHas('user_emails', [
            'email' => 'john.work@example.com',
            'is_primary' => false
        ]);
    }

    public function test_can_get_specific_user(): void
    {
        $user = User::factory()->create();
        UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'test@example.com',
            'is_primary' => true
        ]);

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'full_name',
                    'phone',
                    'primary_email',
                    'emails'
                ]
            ]);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create();

        $updateData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone' => '+9876543210'
        ];

        $response = $this->putJson("/api/v1/users/{$user->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone' => '+9876543210'
        ]);
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_send_welcome_email_success(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        UserEmail::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->postJson("/api/v1/users/{$user->id}/send-welcome");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'emails_count',
                'emails'
            ]);

        Queue::assertPushed(\App\Jobs\SendWelcomeEmailJob::class, 2);
    }

    public function test_send_welcome_email_fails_when_no_emails(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson("/api/v1/users/{$user->id}/send-welcome");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'User has no email addresses'
            ]);
    }

    public function test_validation_errors_on_create(): void
    {
        $response = $this->postJson('/api/v1/users', [
            'first_name' => '',
            'emails' => []
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'emails']);
    }
}

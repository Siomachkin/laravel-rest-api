<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserEmail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserEmailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_user_emails(): void
    {
        $user = User::factory()->create();
        UserEmail::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/users/{$user->id}/emails");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'email',
                        'is_primary',
                        'verified_at',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    public function test_can_add_email_to_user(): void
    {
        $user = User::factory()->create();

        $emailData = [
            'email' => 'new@example.com',
            'is_primary' => false
        ];

        $response = $this->postJson("/api/v1/users/{$user->id}/emails", $emailData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('user_emails', [
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'is_primary' => false
        ]);
    }

    public function test_can_update_email(): void
    {
        $user = User::factory()->create();
        $email = UserEmail::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'email' => 'updated@example.com',
            'is_primary' => true
        ];

        $response = $this->putJson("/api/v1/users/{$user->id}/emails/{$email->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_emails', [
            'id' => $email->id,
            'email' => 'updated@example.com',
            'is_primary' => true
        ]);
    }

    public function test_can_delete_email(): void
    {
        $user = User::factory()->create();
        $email = UserEmail::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/users/{$user->id}/emails/{$email->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('user_emails', ['id' => $email->id]);
    }

    public function test_can_set_primary_email(): void
    {
        $user = User::factory()->create();

        $email1 = UserEmail::factory()->create([
            'user_id' => $user->id,
            'is_primary' => true
        ]);

        $email2 = UserEmail::factory()->create([
            'user_id' => $user->id,
            'is_primary' => false
        ]);

        $response = $this->patchJson("/api/v1/users/{$user->id}/emails/{$email2->id}/set-primary");

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_emails', [
            'id' => $email1->id,
            'is_primary' => false
        ]);

        $this->assertDatabaseHas('user_emails', [
            'id' => $email2->id,
            'is_primary' => true
        ]);
    }
}

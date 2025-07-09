<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserEmail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_multiple_emails(): void
    {
        $user = User::factory()->create();

        $email1 = UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'test1@example.com'
        ]);

        $email2 = UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'test2@example.com'
        ]);

        $this->assertEquals(2, $user->emails->count());
        $this->assertTrue($user->emails->contains($email1));
        $this->assertTrue($user->emails->contains($email2));
    }

    public function test_user_full_name_accessor(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    public function test_user_primary_email_accessor(): void
    {
        $user = User::factory()->create();

        UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'primary@example.com',
            'is_primary' => true
        ]);

        UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'secondary@example.com',
            'is_primary' => false
        ]);

        $this->assertEquals('primary@example.com', $user->primary_email);
    }

    public function test_user_can_add_email(): void
    {
        $user = User::factory()->create();

        $email = $user->addEmail('new@example.com', true);

        $this->assertInstanceOf(UserEmail::class, $email);
        $this->assertEquals('new@example.com', $email->email);
        $this->assertTrue($email->is_primary);
    }

    public function test_adding_primary_email_sets_others_as_non_primary(): void
    {
        $user = User::factory()->create();

        $existingEmail = UserEmail::factory()->create([
            'user_id' => $user->id,
            'is_primary' => true
        ]);

        $user->addEmail('new@example.com', true);

        $this->assertFalse($existingEmail->fresh()->is_primary);
    }

    public function test_user_can_set_primary_email(): void
    {
        $user = User::factory()->create();

        $email1 = UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'first@example.com',
            'is_primary' => true
        ]);

        $email2 = UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'second@example.com',
            'is_primary' => false
        ]);

        $result = $user->setPrimaryEmail('second@example.com');

        $this->assertTrue($result);
        $this->assertFalse($email1->fresh()->is_primary);
        $this->assertTrue($email2->fresh()->is_primary);
    }
}

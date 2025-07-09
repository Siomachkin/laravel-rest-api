<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserEmail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_email_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $email = UserEmail::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $email->user);
        $this->assertEquals($user->id, $email->user->id);
    }

    public function test_primary_scope_returns_only_primary_emails(): void
    {
        $user = User::factory()->create();

        UserEmail::factory()->create([
            'user_id' => $user->id,
            'is_primary' => true
        ]);

        UserEmail::factory()->create([
            'user_id' => $user->id,
            'is_primary' => false
        ]);

        $primaryEmails = UserEmail::primary()->get();

        $this->assertEquals(1, $primaryEmails->count());
        $this->assertTrue($primaryEmails->first()->is_primary);
    }
}

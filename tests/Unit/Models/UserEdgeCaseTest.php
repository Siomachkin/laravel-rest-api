<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserEmail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_user_cascades_emails(): void
    {
        $user = User::factory()->create();
        
        $email1 = UserEmail::factory()->create(['user_id' => $user->id]);
        $email2 = UserEmail::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseHas('user_emails', ['id' => $email1->id]);
        $this->assertDatabaseHas('user_emails', ['id' => $email2->id]);

        $user->delete();

        $this->assertDatabaseMissing('user_emails', ['id' => $email1->id]);
        $this->assertDatabaseMissing('user_emails', ['id' => $email2->id]);
    }

    public function test_deleting_primary_email_leaves_no_primary(): void
    {
        $user = User::factory()->create();
        
        $primaryEmail = UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'primary@example.com',
            'is_primary' => true
        ]);
        
        $secondaryEmail = UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'secondary@example.com',
            'is_primary' => false
        ]);

        $primaryEmail->delete();
        $secondaryEmail->refresh();

        $this->assertFalse($secondaryEmail->is_primary);
        $this->assertNull($user->fresh()->primary_email);
    }

    public function test_adding_primary_email_when_none_exists(): void
    {
        $user = User::factory()->create();
        
        $email = $user->addEmail('test@example.com', true);
        
        $this->assertTrue($email->is_primary);
        $this->assertEquals('test@example.com', $user->fresh()->primary_email);
    }

    public function test_adding_multiple_primary_emails_keeps_only_one(): void
    {
        $user = User::factory()->create();
        
        $email1 = $user->addEmail('first@example.com', true);
        $email2 = $user->addEmail('second@example.com', true);
        
        $email1->refresh();
        
        $this->assertFalse($email1->is_primary);
        $this->assertTrue($email2->is_primary);
        $this->assertEquals('second@example.com', $user->fresh()->primary_email);
    }

    public function test_setting_primary_email_with_nonexistent_email(): void
    {
        $user = User::factory()->create();
        
        $result = $user->setPrimaryEmail('nonexistent@example.com');
        
        $this->assertFalse($result);
        $this->assertNull($user->fresh()->primary_email);
    }

    public function test_user_with_no_emails_has_null_primary_email(): void
    {
        $user = User::factory()->create();
        
        $this->assertNull($user->primary_email);
        $this->assertEquals(0, $user->emails->count());
    }

    public function test_user_with_only_non_primary_emails(): void
    {
        $user = User::factory()->create();
        
        UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'test1@example.com',
            'is_primary' => false
        ]);
        
        UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'test2@example.com',
            'is_primary' => false
        ]);

        $this->assertNull($user->fresh()->primary_email);
        $this->assertEquals(2, $user->emails->count());
    }

    public function test_concurrent_primary_email_setting(): void
    {
        $user = User::factory()->create();
        
        $email1 = UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'email1@example.com',
            'is_primary' => false
        ]);
        
        $email2 = UserEmail::factory()->create([
            'user_id' => $user->id,
            'email' => 'email2@example.com',
            'is_primary' => false
        ]);

        $user->setPrimaryEmail('email1@example.com');
        $user->setPrimaryEmail('email2@example.com');

        $email1->refresh();
        $email2->refresh();

        $this->assertFalse($email1->is_primary);
        $this->assertTrue($email2->is_primary);
    }

    public function test_user_full_name_with_empty_names(): void
    {
        $user = User::factory()->create([
            'first_name' => '',
            'last_name' => ''
        ]);

        $this->assertEquals(' ', $user->full_name);
    }

    public function test_user_full_name_with_whitespace(): void
    {
        $user = User::factory()->create([
            'first_name' => '  John  ',
            'last_name' => '  Doe  '
        ]);

        $this->assertEquals('  John     Doe  ', $user->full_name);
    }
}
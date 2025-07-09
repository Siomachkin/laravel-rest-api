<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendWelcomeEmailJob;
use App\Mail\WelcomeMail;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_welcome_email(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $email = 'test@example.com';

        $job = new SendWelcomeEmailJob($user, $email);
        $job->handle();

        Mail::assertSent(WelcomeMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    public function test_job_is_queued_on_emails_queue(): void
    {
        $user = User::factory()->create();
        $email = 'test@example.com';

        $job = new SendWelcomeEmailJob($user, $email);

        $this->assertEquals('emails', $job->queue);
    }
}

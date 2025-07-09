<?php

namespace App\Jobs;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;
    public string $emailAddress;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(User $user, string $emailAddress)
    {
        $this->user = $user;
        $this->emailAddress = $emailAddress;

        $this->onQueue('emails');
    }

    public function handle(): void
    {
        try {
            Mail::to($this->emailAddress)
                ->send(new WelcomeMail($this->user));

            Log::info('Welcome email sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->emailAddress
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $this->user->id,
                'email' => $this->emailAddress,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Welcome email job failed permanently', [
            'user_id' => $this->user->id,
            'email' => $this->emailAddress,
            'error' => $exception->getMessage()
        ]);
    }
}

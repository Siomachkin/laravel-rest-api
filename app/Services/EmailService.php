<?php

namespace App\Services;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EmailService
{
    public function sendWelcomeEmails(User $user): array
    {
        $user->load('emails');

        if ($user->emails->isEmpty()) {
            return [
                'success' => false,
                'message' => 'User has no email addresses'
            ];
        }

        foreach ($user->emails as $userEmail) {
            SendWelcomeEmailJob::dispatch($user, $userEmail->email)
                ->delay(now()->addSeconds(rand(2, 15)));
        }

        Log::info('Welcome email jobs dispatched', [
            'user_id' => $user->id,
            'emails_count' => $user->emails->count()
        ]);

        return [
            'success' => true,
            'message' => "Welcome email job queued for {$user->emails->count()} email addresses",
            'emails_count' => $user->emails->count(),
            'emails' => $user->emails->pluck('email')->toArray()
        ];
    }
}

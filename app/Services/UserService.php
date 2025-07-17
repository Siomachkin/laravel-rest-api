<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function getUsersPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with(['emails'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            });

        return $query->paginate($perPage);
    }

    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
            ]);

            if (isset($data['emails']) && is_array($data['emails'])) {
                $this->syncUserEmails($user, $data['emails']);
            }

            Log::info('User created', ['user_id' => $user->id]);

            return $user->load('emails');
        });
    }

    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update([
                'first_name' => $data['first_name'] ?? $user->first_name,
                'last_name' => $data['last_name'] ?? $user->last_name,
                'phone' => $data['phone'] ?? $user->phone,
                'password' => $data['password'] ?? $user->password,
            ]);

            if (isset($data['emails']) && is_array($data['emails'])) {
                $this->syncUserEmails($user, $data['emails']);
            }

            Log::info('User updated', ['user_id' => $user->id]);

            return $user->load('emails');
        });
    }

    public function deleteUser(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $userId = $user->id;
            $user->emails()->delete();
            $deleted = $user->delete();

            Log::info('User deleted', ['user_id' => $userId]);

            return $deleted;
        });
    }

    public function getUserEmails(User $user): Collection
    {
        return $user->emails()->orderBy('is_primary', 'desc')->get();
    }

    public function addEmailToUser(User $user, string $email, bool $isPrimary = false): UserEmail
    {
        return DB::transaction(function () use ($user, $email, $isPrimary) {
            $userEmail = $user->addEmail($email, $isPrimary);

            Log::info('Email added to user', [
                'user_id' => $user->id,
                'email' => $email,
                'is_primary' => $isPrimary
            ]);

            return $userEmail;
        });
    }

    public function updateUserEmail(User $user, UserEmail $email, array $data): UserEmail
    {
        return DB::transaction(function () use ($user, $email, $data) {
            if (isset($data['is_primary']) && $data['is_primary']) {
                $user->setPrimaryEmail($email->email);
            }

            $email->update($data);

            Log::info('User email updated', [
                'user_id' => $user->id,
                'email_id' => $email->id
            ]);

            return $email;
        });
    }

    public function deleteUserEmail(User $user, UserEmail $email): bool
    {
        return DB::transaction(function () use ($user, $email) {
            if ($email->is_primary && $user->emails()->count() > 1) {
                throw new \InvalidArgumentException('Cannot delete primary email address. Set another email as primary first.');
            }

            $emailId = $email->id;
            $deleted = $email->delete();

            Log::info('User email deleted', [
                'user_id' => $user->id,
                'email_id' => $emailId
            ]);

            return $deleted;
        });
    }

    public function setPrimaryEmail(User $user, UserEmail $email): UserEmail
    {
        return DB::transaction(function () use ($user, $email) {
            $user->setPrimaryEmail($email->email);

            Log::info('Primary email changed', [
                'user_id' => $user->id,
                'email_id' => $email->id
            ]);

            return $email->fresh();
        });
    }

    private function syncUserEmails(User $user, array $emails): void
    {
        $user->emails()->delete();

        $hasPrimary = false;
        foreach ($emails as $index => $emailData) {
            $isPrimary = $emailData['is_primary'] ?? false;
            
            if (!$hasPrimary && ($isPrimary || $index === 0)) {
                $isPrimary = true;
                $hasPrimary = true;
            } elseif ($hasPrimary && $isPrimary) {
                $isPrimary = false;
            }

            $user->addEmail($emailData['email'], $isPrimary);
        }
    }
}
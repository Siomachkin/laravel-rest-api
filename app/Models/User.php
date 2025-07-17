<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Relationships

    public function emails(): HasMany
    {
        return $this->hasMany(UserEmail::class);
    }

    public function primaryEmail()
    {
        return $this->hasOne(UserEmail::class)->where('is_primary', true);
    }

    // Accessors

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getPrimaryEmailAttribute(): ?string
    {
        return $this->primaryEmail()->first()?->email;
    }


    // Custom methods

    public function addEmail(string $email, bool $isPrimary = false): UserEmail
    {
        if ($isPrimary) {
            $this->emails()->update(['is_primary' => false]);
        }

        return $this->emails()->create([
            'email' => $email,
            'is_primary' => $isPrimary,
        ]);
    }

    public function setPrimaryEmail(string $email): bool
    {
        $this->emails()->update(['is_primary' => false]);
        
        return $this->emails()
            ->where('email', $email)
            ->update(['is_primary' => true]) > 0;
    }
}

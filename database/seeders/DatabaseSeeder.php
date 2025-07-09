<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserEmail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create test admin user
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'phone' => '+48123456789',
            'password' => Hash::make('password'),
        ]);

        // Add email addresses for admin
        $admin->addEmail('admin@example.com', true); // Primary email
        $admin->addEmail('admin.backup@example.com', false); // Secondary email

        // Create test user John Doe
        $john = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+48987654321',
            'password' => Hash::make('password'),
        ]);

        // Add multiple email addresses for John
        $john->addEmail('john.doe@example.com', true);
        $john->addEmail('john.work@company.com', false);
        $john->addEmail('john.personal@gmail.com', false);

        // Create user with Polish name
        $anna = User::create([
            'first_name' => 'Anna',
            'last_name' => 'Kowalska',
            'phone' => '+48555123456',
            'password' => Hash::make('password'),
        ]);

        $anna->addEmail('anna.kowalska@example.pl', true);
        $anna->addEmail('a.kowalska@work.pl', false);

        // Create user without phone number
        $mike = User::create([
            'first_name' => 'Mike',
            'last_name' => 'Johnson',
            'phone' => null,
            'password' => Hash::make('password'),
        ]);

        $mike->addEmail('mike.johnson@example.com', true);

        // Create user with verified email addresses
        $sarah = User::create([
            'first_name' => 'Sarah',
            'last_name' => 'Connor',
            'phone' => '+48777888999',
            'password' => Hash::make('password'),
        ]);

        // Create email addresses with verification
        UserEmail::create([
            'user_id' => $sarah->id,
            'email' => 'sarah.connor@example.com',
            'is_primary' => true,
            'verified_at' => now(),
        ]);

        UserEmail::create([
            'user_id' => $sarah->id,
            'email' => 'sarah.work@terminator.com',
            'is_primary' => false,
            'verified_at' => now()->subDays(5),
        ]);

        // Create user with unverified email addresses
        $bob = User::create([
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'phone' => '+48111222333',
            'password' => Hash::make('password'),
        ]);

        UserEmail::create([
            'user_id' => $bob->id,
            'email' => 'bob.smith@example.com',
            'is_primary' => true,
            'verified_at' => null,
        ]);

        UserEmail::create([
            'user_id' => $bob->id,
            'email' => 'bob.alternative@example.com',
            'is_primary' => false,
            'verified_at' => null,
        ]);

        // Create random users using factory
        User::factory(15)->create()->each(function ($user) {
            $emailCount = fake()->numberBetween(1, 4);

            for ($i = 0; $i < $emailCount; $i++) {
                $isPrimary = $i === 0; // First email is always primary

                $user->addEmail(
                    fake()->unique()->safeEmail(),
                    $isPrimary
                );
            }
        });

        // Create users with corporate email addresses
        $companies = ['microsoft.com', 'google.com', 'apple.com', 'amazon.com'];

        foreach ($companies as $company) {
            $user = User::factory()->create();

            $user->addEmail(
                strtolower($user->first_name . '.' . $user->last_name) . '@' . $company,
                true
            );

            // Add additional personal email
            $user->addEmail(fake()->safeEmail(), false);
        }

        // Create users with long names for testing
        $longNameUser = User::create([
            'first_name' => 'Christopher',
            'last_name' => 'Montgomery-Richardson',
            'phone' => '+48999888777',
            'password' => Hash::make('password'),
        ]);

        $longNameUser->addEmail('christopher.montgomery@example.com', true);
        $longNameUser->addEmail('c.montgomery.richardson@work.com', false);

        // Create users for edge case testing
        $this->createEdgeCaseUsers();

        echo "✅ Seeder completed successfully!\n";
        echo "📊 Created users:\n";
        echo "   - Admin User (admin@example.com)\n";
        echo "   - John Doe (john.doe@example.com)\n";
        echo "   - Anna Kowalska (anna.kowalska@example.pl)\n";
        echo "   - Mike Johnson (mike.johnson@example.com)\n";
        echo "   - Sarah Connor (sarah.connor@example.com)\n";
        echo "   - Bob Smith (bob.smith@example.com)\n";
        echo "   - 15 random users with factories\n";
        echo "   - 4 corporate users\n";
        echo "   - 1 user with long name\n";
        echo "   - Edge case users\n";
        echo "📧 Total users: " . User::count() . "\n";
        echo "📧 Total emails: " . UserEmail::count() . "\n";
    }

    private function createEdgeCaseUsers(): void
    {
        // User with maximum emails (for testing limits)
        $maxEmailUser = User::create([
            'first_name' => 'Max',
            'last_name' => 'Emails',
            'phone' => '+48123000000',
            'password' => Hash::make('password'),
        ]);

        // Add 20 emails to test pagination and limits
        for ($i = 1; $i <= 20; $i++) {
            $maxEmailUser->addEmail("max.email{$i}@example.com", $i === 1);
        }

        // User with special characters in name
        $specialUser = User::create([
            'first_name' => 'José',
            'last_name' => 'García-López',
            'phone' => '+48456000000',
            'password' => Hash::make('password'),
        ]);

        $specialUser->addEmail('jose.garcia@example.com', true);

        // User with very long phone number
        $longPhoneUser = User::create([
            'first_name' => 'Long',
            'last_name' => 'Phone',
            'phone' => '+48123456789012345',
            'password' => Hash::make('password'),
        ]);

        $longPhoneUser->addEmail('long.phone@example.com', true);
    }
}

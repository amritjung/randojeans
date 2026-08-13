<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate checks if a user with this email already exists
        // before creating one — makes this seeder safe to run multiple
        // times (e.g. if you seed without migrate:fresh) without erroring
        // on a duplicate email or creating a second admin account.
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@randojeans.test')],
            [
                'name' => 'Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'is_admin' => true,
                // Marking email as already verified means the admin
                // account can log into /admin immediately without
                // needing to click a verification link first.
                'email_verified_at' => now(),
            ]
        );
    }
}
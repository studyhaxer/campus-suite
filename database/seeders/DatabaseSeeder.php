<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // No demo organization or user is seeded here on purpose — this app
        // is invite-only by design. Create the first organization + Manager
        // account with:
        //
        //   php artisan app:create-manager
        //
        // which prompts for the details interactively so no credentials
        // ever live in source control.
    }
}
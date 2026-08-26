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

    $organization = \App\Models\Organization::firstOrCreate(
        ['slug' => 'demo-org'],
        ['name' => 'Demo Organization']
    );

    $manager = \App\Models\User::firstOrCreate(
        ['email' => 'manager@example.com'],
        [
            'name' => 'Demo Manager',
            'password' => bcrypt('password'),
            'organization_id' => $organization->id,
        ]
    );

    $manager->assignRole('Manager');
    }
}

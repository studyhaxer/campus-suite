<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Manager', 'Campus Admin', 'Accountant', 'Teacher'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateManagerAccount extends Command
{
    protected $signature = 'app:create-manager';

    protected $description = 'Interactively create an organization and its first Manager account';

    public function handle(): int
    {
        $this->info('Create the first Manager account for a new organization.');

        $orgName = $this->ask('Organization name');

        $name = $this->ask('Manager full name');

        $email = $this->ask('Manager email');
        $emailValidator = Validator::make(['email' => $email], ['email' => 'required|email|unique:users,email']);
        if ($emailValidator->fails()) {
            $this->error($emailValidator->errors()->first('email'));
            return self::FAILURE;
        }

        $password = $this->secret('Manager password (min 8 characters, hidden while typing)');
        $confirm = $this->secret('Confirm password');

        if ($password !== $confirm) {
            $this->error('Passwords do not match.');
            return self::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return self::FAILURE;
        }

        $organization = Organization::create([
            'name' => $orgName,
            'slug' => Str::slug($orgName) . '-' . Str::random(4),
        ]);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'organization_id' => $organization->id,
            'email_verified_at' => now(),
        ]);

        $user->assignRole('Manager');

        $this->newLine();
        $this->info("Organization \"{$organization->name}\" created.");
        $this->info("Manager account created for {$email}.");
        $this->comment('Next: log in and create the first campus from the Campuses page.');

        return self::SUCCESS;
    }
}
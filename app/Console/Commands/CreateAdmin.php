<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('app:create-admin')]
#[Description('Create an administrator account without exposing its password in source control.')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $name = trim($this->ask('Administrator name'));
        $email = mb_strtolower(trim($this->ask('Administrator email')));
        $password = $this->secret('Password (minimum 12 characters)');
        $confirmation = $this->secret('Confirm password');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:100', 'unique:users,email'],
                'password' => ['required', 'string', 'min:12'],
            ],
        );

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            return self::FAILURE;
        }

        if (! is_string($password) || ! hash_equals($password, (string) $confirmation)) {
            $this->components->error('Passwords do not match.');

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_admin' => true,
        ]);

        $this->components->info('Administrator account created.');

        return self::SUCCESS;
    }
}

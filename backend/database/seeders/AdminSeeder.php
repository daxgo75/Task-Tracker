<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@tasktracker.com'],
            [
                'name'     => 'Admin',
                'email'    => 'admin@tasktracker.com',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );
    }
}

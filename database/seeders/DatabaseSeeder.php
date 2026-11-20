<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'Administrator',
                'role'     => 'admin',
                'password' => 'admin123', // otomatis di-hash oleh cast di model User
            ]
        );
    }
}
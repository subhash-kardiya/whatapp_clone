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
        // Create test users with identical password for easy login testing
        $password = bcrypt('password');

        User::create([
            'name' => 'Subhash Kardiya',
            'email' => 'subhash@example.com',
            'phone' => '9876543210',
            'password' => $password,
            'about' => 'How is everything? ☕',
        ]);

        User::create([
            'name' => 'Aarav Patel',
            'email' => 'aarav@example.com',
            'phone' => '9876543211',
            'password' => $password,
            'about' => 'Busy. Can you talk later?',
        ]);

        User::create([
            'name' => 'Diya Sharma',
            'email' => 'diya@example.com',
            'phone' => '9876543212',
            'password' => $password,
            'about' => 'Har Har Mahadev 🔱',
        ]);

        User::create([
            'name' => 'Rahul Mehta',
            'email' => 'rahul@example.com',
            'phone' => '9876543213',
            'password' => $password,
            'about' => 'Hey there! I am using WhatsApp.',
        ]);
    }
}

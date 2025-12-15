<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Akun Super Admin
        User::create([
            'name' => 'Sawunggaling Super',
            'email' => 'super@gmail.com', 
            'password' => Hash::make('password'), 
            'role' => 'Super', 
            'random_key' => Str::random(60),
        ]);

        // 2. Akun Resepsionis
        User::create([
            'name' => 'Staff Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'Admin',
            'random_key' => Str::random(60),
        ]);

        // 3. Akun Manager
        User::create([
            'name' => 'Manager Hotel',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'Manager',
            'random_key' => Str::random(60),
        ]);

        // 4. Akun Dapur
        User::create([
            'name' => 'Kepala Dapur',
            'email' => 'dapur@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'Dapur',
            'random_key' => Str::random(60),
        ]);

        User::create([
            'name' => 'Housekeeping',
            'email' => 'housekeeping@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'Housekeeping',
            'random_key' => Str::random(60),
        ]);
    }
}
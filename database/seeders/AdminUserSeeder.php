<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'Administrador',
                'email'    => 'admin@admin.com',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ]
        );
    }
}


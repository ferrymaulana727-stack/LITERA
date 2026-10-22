<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Gramedia',
                'email' => 'admin@gramedia.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Staff Toko',
                'email' => 'staff@gramedia.test',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ],
            [
                'name' => 'Customer Setia',
                'email' => 'customer@gramedia.test',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}

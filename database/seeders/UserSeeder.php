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
                'name' => 'Admin User',
                'email' => 'admin@kushaos.test',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Operator One',
                'email' => 'operator1@kushaos.test',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Operator Two',
                'email' => 'operator2@kushaos.test',
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}

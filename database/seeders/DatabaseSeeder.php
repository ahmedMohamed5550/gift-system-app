<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'مالك المتجر',
                'password' => 'password',
                'role' => 'owner',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['username' => 'cashier'],
            [
                'name' => 'موظف مبيعات',
                'password' => 'password',
                'role' => 'employee',
                'is_active' => true,
            ]
        );
    }
}

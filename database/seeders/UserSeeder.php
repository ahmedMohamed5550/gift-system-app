<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
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

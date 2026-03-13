<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = 'admin@admin.com';
        User::firstOrCreate(['email' => $adminEmail], [
            'name' => 'admin',
            'email' => $adminEmail,
            'password' => 'admin',
        ]);
    }
}

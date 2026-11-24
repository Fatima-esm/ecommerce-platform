<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'ahmad@gmail.com'],
            [
                'name' => 'ahmad',
                'password' => Hash::make('12345678'),
            ]
        );

        $this->call([
            AdminUserSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
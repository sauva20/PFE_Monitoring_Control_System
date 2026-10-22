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
        User::create([
            'name' => 'Manager Keselamatan',
            'email' => 'head@ehss.id',
            'password' => Hash::make('password'),
            'role' => 'Head of EHSS, SM, OE & LPMO',
            'pin' => '123456',
        ]);

        User::create([
            'name' => 'Andi Rahman',
            'email' => 'andi@ehss.id',
            'password' => Hash::make('password'),
            'role' => 'EHSS',
            'pin' => '654321',
        ]);
    }
}

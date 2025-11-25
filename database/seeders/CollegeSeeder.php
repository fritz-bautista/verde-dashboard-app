<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CollegeSeeder extends Seeder
{
    public function run(): void
    {
        // --- LEVELS ---
        DB::table('levels')->insert([
            ['name' => 'Level 1', 'floor_number' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Level 2', 'floor_number' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Level 3', 'floor_number' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Level 4', 'floor_number' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Level 5', 'floor_number' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Level 6', 'floor_number' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Level 7', 'floor_number' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // --- COLLEGES per floor ---
        $colleges = [
            1 => 'College of International Hospitality Management',
            2 => 'College of Business and Accountancy',
            3 => 'College of Education',
            4 => 'College of Nursing',
            5 => 'College of Engineering',
            6 => 'College of Computer Studies',
            7 => 'College of Arts and Sciences',
        ];

        foreach ($colleges as $floor => $name) {
            DB::table('colleges')->insert([
                'floor' => $floor,
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // --- USERS ---
        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'remember_token' => Str::random(10),
            'qr_code' => Str::uuid()->toString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // --- BASE BINS ---
        DB::table('bins')->insert([
            ['level_id' => 1, 'type' => 'Recyclable - Bottled Water', 'created_at' => now(), 'updated_at' => now()],
            ['level_id' => 1, 'type' => 'Recyclable - Paper', 'created_at' => now(), 'updated_at' => now()],
            ['level_id' => 6, 'type' => 'Recyclable - Bottled Water', 'created_at' => now(), 'updated_at' => now()],
            ['level_id' => 6, 'type' => 'Recyclable - Paper', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

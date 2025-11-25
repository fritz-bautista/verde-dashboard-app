<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SmartBinSampleSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear tables
        DB::table('waste_levels')->truncate();
        DB::table('bins')->truncate();
        DB::table('levels')->truncate();
        DB::table('colleges')->truncate();
        DB::table('users')->truncate();

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = Carbon::now();

        // --- LEVELS ---
        $levels = [
            ['name' => 'Level 1', 'floor_number' => 1],
            ['name' => 'Level 2', 'floor_number' => 2],
            ['name' => 'Level 3', 'floor_number' => 3],
            ['name' => 'Level 4', 'floor_number' => 4],
            ['name' => 'Level 5', 'floor_number' => 5],
            ['name' => 'Level 6', 'floor_number' => 6],
            ['name' => 'Level 7', 'floor_number' => 7],
        ];

        foreach ($levels as $level) {
            DB::table('levels')->insert([
                'name' => $level['name'],
                'floor_number' => $level['floor_number'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // --- COLLEGES ---
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
                'points' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // --- ADMIN USER ---
        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => $now,
            'password' => Hash::make('password123'),
            'remember_token' => Str::random(10),
            'qr_code' => Str::uuid()->toString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // --- BINS ---
        $levelsData = DB::table('levels')->get()->keyBy('floor_number');

        $binData = [
            // Level 6 bins (College of Computer Studies)
            ['level' => 6, 'type' => 'Recyclable - Bottled Water', 'capacity' => 50],
            ['level' => 6, 'type' => 'Recyclable - Paper', 'capacity' => 50],
        ];

        foreach ($binData as $bin) {
            DB::table('bins')->insert([
                'level_id' => $levelsData[$bin['level']]->id,
                'type' => $bin['type'],
                'capacity' => $bin['capacity'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // --- WASTE LEVELS FOR 6th FLOOR ---
        $collegeLevel = $levelsData[6];
        $bins6th = DB::table('bins')->where('level_id', $collegeLevel->id)->get()->keyBy('type');

        $week1 = $now->copy()->subWeeks(2);
        $week2 = $now->copy()->subWeek();

        // Week 1: Full
        DB::table('waste_levels')->insert([
            [
                'bin_id' => $bins6th['Recyclable - Bottled Water']->id,
                'weight' => 50,
                'level' => 100,
                'created_at' => $week1,
                'updated_at' => $week1,
            ],
            [
                'bin_id' => $bins6th['Recyclable - Paper']->id,
                'weight' => 40,
                'level' => 80,
                'created_at' => $week1->copy()->addDay(),
                'updated_at' => $week1->copy()->addDay(),
            ],
        ]);

        // Week 2: Half
        DB::table('waste_levels')->insert([
            [
                'bin_id' => $bins6th['Recyclable - Bottled Water']->id,
                'weight' => 25,
                'level' => 50,
                'created_at' => $week2,
                'updated_at' => $week2,
            ],
            [
                'bin_id' => $bins6th['Recyclable - Paper']->id,
                'weight' => 20,
                'level' => 40,
                'created_at' => $week2,
                'updated_at' => $week2,
            ],
        ]);

        // --- CALCULATE POINTS FOR COLLEGE OF COMPUTER STUDIES ---
        $totalWeight = DB::table('waste_levels')
            ->whereIn('bin_id', [
                $bins6th['Recyclable - Bottled Water']->id,
                $bins6th['Recyclable - Paper']->id,
            ])->sum('weight');

        DB::table('colleges')->where('floor', 6)->update([
            'points' => $totalWeight * 10
        ]);

        $points = $totalWeight * 10;
        $this->command->info("✅ Sample data inserted. College of Computer Studies now has {$points} points.");
    }
}

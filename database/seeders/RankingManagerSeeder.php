<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RankingManagerSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ranking_managers')->truncate();

        $now = Carbon::now();

        DB::table('ranking_managers')->insert([
            [
                'semester_name' => '1st Semester 2025-2026',
                'is_active' => true,
                'started_at' => $now->copy()->subWeeks(2),
                'stopped_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'semester_name' => '2nd Semester 2024-2025',
                'is_active' => false,
                'started_at' => $now->copy()->subMonths(6),
                'stopped_at' => $now->copy()->subMonths(3),
                'created_at' => $now->copy()->subMonths(6),
                'updated_at' => $now->copy()->subMonths(3),
            ],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WasteLevel;
use Carbon\Carbon;

class MonthlyWasteSeeder extends Seeder
{
    public function run()
    {
        // Define bins with type
        $bins = [
            ['id' => 1, 'type' => 'Recyclable - Bottled Water'],
            ['id' => 2, 'type' => 'Recyclable - Paper'],
            ['id' => 3, 'type' => 'Recyclable - Bottled Water'],
        ];

        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        foreach ($bins as $bin) {
            $currentWeight = 0;

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

                // Determine weekday (1=Mon, 7=Sun)
                $weekday = $date->dayOfWeekIso;

                // Time slots from 7 AM to 8 PM
                $hour = 7;
                while ($hour <= 20) {
                    $interval = rand(1, 2); // 1-2 hour intervals
                    $timestamp = $date->copy()->setHour($hour)->setMinute(rand(0, 59));

                    // Base accumulation depending on bin type
                    if ($bin['type'] === 'Recyclable - Bottled Water') {
                        $baseAdd = ($weekday <= 5) ? rand(20, 300) / 100 : rand(5, 100) / 100; // More on school days
                    } else { // Paper
                        $baseAdd = ($weekday <= 5) ? rand(10, 150) / 100 : rand(5, 100) / 100;
                    }

                    // Slight variation by hour: more accumulation during breaks
                    if (in_array($hour, [7, 10, 12, 15, 17])) {
                        $baseAdd *= rand(110, 140) / 100; // 10%-40% more
                    }

                    $currentWeight += $baseAdd;

                    // Simulate collection if >40kg
                    if ($currentWeight >= 40) {
                        $currentWeight = rand(0, 3); // leftover after collection
                    }

                    // Cap at 50kg
                    if ($currentWeight > 50) $currentWeight = 50;

                    WasteLevel::create([
                        'bin_id' => $bin['id'],
                        'weight' => round($currentWeight, 2),
                        'level' => round(($currentWeight / 50) * 100, 2),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);

                    $hour += $interval;
                }
            }
        }
    }
}

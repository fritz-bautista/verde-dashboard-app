<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\WasteLevel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\College;
use Illuminate\Http\Request;
use App\Models\RankingManager;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            // --- Filter parameter: weekly | monthly | semester ---
            $filter = $request->input('filter', 'monthly');
            $now = Carbon::now();

            switch ($filter) {
                case 'weekly':
                    $startDate = $now->copy()->startOfWeek();
                    $endDate = $now->copy()->endOfWeek();
                    break;

                case 'semester':
                    if ($now->month >= 1 && $now->month <= 5) {
                        $startDate = Carbon::create($now->year, 1, 1);
                        $endDate = Carbon::create($now->year, 5, 31);
                    } else {
                        $startDate = Carbon::create($now->year, 6, 1);
                        $endDate = Carbon::create($now->year, 10, 31);
                    }
                    break;

                default: // monthly
                    $startDate = $now->copy()->startOfMonth();
                    $endDate = $now->copy()->endOfMonth();
                    break;
            }

            // --- Incremental KPI calculation (only for positive changes) ---
            $calculateIncrementalTotal = function ($typePattern) use ($startDate, $endDate) {
                return WasteLevel::whereHas('bin', fn($q) => $q->where('type', 'like', $typePattern))
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get()
                    ->groupBy('bin_id')
                    ->map(function ($binLevels) {
                        $binLevels = $binLevels->sortBy('created_at')->values();
                        $total = 0;
                        for ($i = 0; $i < $binLevels->count(); $i++) {
                            if ($i === 0) {
                                // First record: count as is (assuming initial trash added)
                                $total += $binLevels[$i]->weight;
                            } else {
                                // Only add positive differences (ignore trash removal)
                                $diff = $binLevels[$i]->weight - $binLevels[$i - 1]->weight;
                                $total += ($diff > 0) ? $diff : 0;
                            }
                        }
                        return $total;
                    })
                    ->sum();
            };

            // --- Calculate KPIs ---
            $totalBottle = $calculateIncrementalTotal('%Bottled Water%');
            $totalPaper = $calculateIncrementalTotal('%Paper%');

            // --- Levels & latest wasteLevels for bin cards ---
            $levels = Level::with(['bins.wasteLevels' => fn($q) => $q->latest()->limit(1)])->get();

            // --- College rankings using incremental weights ---
            $colleges = College::with([
                'levels.bins.wasteLevels' => fn($q) => $q->where('created_at', '>=', $startDate)
            ])->get();

            $collegeRankings = $colleges->map(function ($college) {
                $totalWeight = 0;
                foreach ($college->levels as $level) {
                    foreach ($level->bins as $bin) {
                        $binLevels = $bin->wasteLevels->sortBy('created_at')->values();
                        for ($i = 0; $i < $binLevels->count(); $i++) {
                            if ($i === 0) {
                                $totalWeight += $binLevels[$i]->weight;
                            } else {
                                $diff = $binLevels[$i]->weight - $binLevels[$i - 1]->weight;
                                $totalWeight += ($diff > 0) ? $diff : 0;
                            }
                        }
                    }
                }
                $college->points = $totalWeight * 10;
                return $college;
            })->sortByDesc('points')->values();

            $current = RankingManager::where('is_active', true)->latest()->first();

            // --- Percentage change for selected period ---
            $periodLength = max(1, $startDate->diffInDays($endDate));
            $prevStart = $startDate->copy()->subDays($periodLength);

            $thisTotal = $calculateIncrementalTotal('%');
            $prevTotal = WasteLevel::whereBetween('created_at', [$prevStart, $startDate])
                ->get()
                ->groupBy('bin_id')
                ->map(function ($binLevels) {
                    $binLevels = $binLevels->sortBy('created_at')->values();
                    $total = 0;
                    for ($i = 0; $i < $binLevels->count(); $i++) {
                        if ($i === 0) {
                            $total += $binLevels[$i]->weight;
                        } else {
                            $diff = $binLevels[$i]->weight - $binLevels[$i - 1]->weight;
                            $total += ($diff > 0) ? $diff : 0;
                        }
                    }
                    return $total;
                })
                ->sum();

            $percentageChange = $prevTotal > 0 ? (($thisTotal - $prevTotal) / $prevTotal) * 100 : 0;

            // --- Call FastAPI for predictions ---
            $predictions = [];

            foreach ($levels as $level) {
                foreach ($level->bins as $bin) {
                    try {
                        $response = Http::post(env('ROUTE_PREDICTION_API') . '/predict', [
                            'bin_id' => $bin->id,
                            'days_ahead' => 7,
                        ]);

                        if ($response->successful()) {
                            $data = $response->json();
                            $pred = $data['predictions'] ?? [];

                            foreach ($pred as $p) {
                                $predictions[] = [
                                    'bin_type' => $bin->type,
                                    'last_weight' => $bin->wasteLevels->first()?->weight ?? 0,
                                    'level_percent' => $bin->wasteLevels->first()?->level ?? 0,
                                    'overflow_probability' => round($p['predicted_overflow'] ?? 0, 2), // <--- fixed
                                    'collection_needed' => $p['collection_needed'] ?? false,
                                    'recommended_date' => $p['date'] ?? now()->format('Y-m-d'),
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        // fallback: empty predictions
                        \Log::error('Prediction API error: ' . $e->getMessage());
                    }
                }
            }

            return view('main.dashboard', compact(
                'levels',
                'totalBottle',
                'totalPaper',
                'predictions',
                'percentageChange',
                'collegeRankings',
                'filter',
                'current'
            ));
        } catch (\Exception $e) {
            \Log::error('Dashboard index error: ' . $e->getMessage());
            return view('dashboard.offline');
        }
    }

    public function getDashboardData(Request $request)
    {
        // --- Determine current month period ---
        $now = \Carbon\Carbon::now();
        $startDate = $now->copy()->startOfMonth();
        $endDate = $now->copy()->endOfMonth();

        // --- Incremental calculation function ---
        $calculateIncrementalTotal = function ($typePattern) use ($startDate, $endDate) {
            return WasteLevel::whereHas('bin', fn($q) => $q->where('type', 'like', $typePattern))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->groupBy('bin_id')
                ->map(function ($binLevels) {
                    $binLevels = $binLevels->sortBy('created_at')->values();
                    $total = 0;
                    for ($i = 1; $i < $binLevels->count(); $i++) { // start at 1 to sum only increments
                        $diff = $binLevels[$i]->weight - $binLevels[$i - 1]->weight;
                        $total += ($diff > 0) ? $diff : 0;
                    }
                    return $total;
                })
                ->sum();
        };

        $totalBottle = $calculateIncrementalTotal('%Bottled Water%');
        $totalPaper = $calculateIncrementalTotal('%Paper%');

        // --- Levels & latest wasteLevels for bin cards ---
        $levels = Level::with(['bins.wasteLevels' => fn($q) => $q->latest()->limit(1)])->get();

        // --- Fake / placeholder predictions (optional, same as before) ---
        $predictions = [];
        foreach ($levels as $level) {
            foreach ($level->bins as $bin) {
                $latest = $bin->wasteLevels->first();
                $predictions[] = [
                    'bin_type' => $bin->type,
                    'last_weight' => $latest?->weight ?? 0,
                    'level_percent' => $latest?->level ?? 0,
                    'overflow_probability' => rand(0, 100),
                    'collection_needed' => rand(0, 1),
                    'recommended_date' => now()->addDays(rand(1, 3))->format('Y-m-d')
                ];
            }
        }

        return response()->json([
            'levels' => $levels,
            'totalBottle' => round($totalBottle, 2),
            'totalPaper' => round($totalPaper, 2),
            'predictions' => $predictions,
        ]);
    }




    public function getWasteData(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $now = Carbon::now();

        switch ($period) {
            case 'weekly':
                $startDate = $now->copy()->subWeeks(4);
                $data = WasteLevel::select(
                    DB::raw('WEEK(created_at) as label'),
                    DB::raw('SUM(weight) as total')
                )
                    ->whereBetween('created_at', [$startDate, $now])
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get()
                    ->map(fn($item, $i) => [
                        'label' => 'Week ' . ($i + 1),
                        'total' => round($item->total, 2)
                    ]);
                break;

            case 'semester':
                $startDate = $now->month <= 5
                    ? Carbon::create($now->year, 1, 1)
                    : Carbon::create($now->year, 6, 1);

                $data = WasteLevel::select(
                    DB::raw('MONTH(created_at) as label'),
                    DB::raw('SUM(weight) as total')
                )
                    ->whereBetween('created_at', [$startDate, $now])
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get()
                    ->map(fn($item) => [
                        'label' => Carbon::create()->month($item->label)->format('M'),
                        'total' => round($item->total, 2)
                    ]);
                break;

            default: // monthly
                $startDate = $now->copy()->startOfYear();
                $data = WasteLevel::select(
                    DB::raw('MONTH(created_at) as label'),
                    DB::raw('SUM(weight) as total')
                )
                    ->whereBetween('created_at', [$startDate, $now])
                    ->groupBy('label')
                    ->orderBy('label')
                    ->get()
                    ->map(fn($item) => [
                        'label' => Carbon::create()->month($item->label)->format('M'),
                        'total' => round($item->total, 2)
                    ]);
                break;
        }

        return response()->json($data);
    }
}

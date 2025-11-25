<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Level;
use App\Models\Bin;

class BinController extends Controller
{
    // ✅ Loads the Bin Manager view
    public function index()
    {
        $levels = Level::all(); // For dropdown
        return view('main.bin_manager', compact('levels'));
    }

    // ✅ Keep your existing getBinStatus unchanged
    public function getBinStatus(Request $request)
    {
        $levelId = $request->query('floor', 1);

        $level = Level::with(['bins.wasteLevels' => fn($q) => $q->latest()->limit(1)])
            ->find($levelId);

        if (!$level) {
            return response()->json([
                'levelName' => null,
                'bins' => []
            ]);
        }

        $bins = $level->bins->map(fn($bin) => [
            'binId' => $bin->id,
            'type' => $bin->type,
            'weight' => $bin->wasteLevels->first()?->weight ?? 0,
            'capacity' => $bin->capacity ?? 50,
            'percent' => round(($bin->wasteLevels->first()?->weight ?? 0) / ($bin->capacity ?? 50) * 100, 2),
        ])->values();

        return response()->json([
            'levelName' => $level->name,
            'bins' => $bins
        ]);
    }

    // ✅ Add back the show() for Bin Statistics
    public function show($id)
    {
        $bin = Bin::with([
            'level',
            'wasteLevels' => fn($q) => $q->orderBy('created_at', 'desc')->take(10)
        ])->findOrFail($id);

        $latest = $bin->wasteLevels->first();
        $weight = $latest?->weight ?? 0;
        $capacity = $bin->capacity ?: 1;
        $percent = round(($weight / $capacity) * 100, 2);

        if ($percent >= 90)
            $status = 'Full';
        elseif ($percent >= 70)
            $status = 'Almost Full';
        elseif ($percent >= 40)
            $status = 'Half';
        else
            $status = 'Empty';

        $history = $bin->wasteLevels
            ->sortByDesc('created_at')  
            ->map(fn($w) => [
                'date' => $w->created_at->format('M d, Y h:i A'),
                'weight' => $w->weight,
                'percent' => round(($w->weight / max($capacity, 1)) * 100, 2),
            ])->values();

        return view('main.bin_statistics', compact('bin', 'weight', 'percent', 'status', 'history'));
    }

    public function getHistory(Request $request, $id)
    {
        $bin = Bin::with('wasteLevels')->findOrFail($id);
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        $query = $bin->wasteLevels()->orderBy('created_at');
        if ($start)
            $query->whereDate('created_at', '>=', $start);
        if ($end)
            $query->whereDate('created_at', '<=', $end);

        $history = $query->get()->map(fn($w) => [
            'date' => $w->created_at->format('M d, Y h:i A'),
            'weight' => $w->weight,
            'percent' => round(($w->weight / max($bin->capacity, 1)) * 100, 2),
            'status' => ($w->weight / max($bin->capacity, 1) * 100 >= 90 ? 'Full' : ($w->weight / max($bin->capacity, 1) * 100 >= 70 ? 'Almost Full' : ($w->weight / max($bin->capacity, 1) * 100 >= 40 ? 'Half' : 'Empty')))
        ])->values();

        return response()->json($history);
    }



    public function store(Request $request)
    {
        $request->validate([
            'level_id' => 'required|exists:levels,id',
            'type' => 'required|string|max:255',
            'capacity' => 'required|numeric|min:1',
        ]);

        $bin = Bin::create([
            'level_id' => $request->level_id,
            'type' => $request->type,
            'capacity' => $request->capacity,
        ]);

        return response()->json(['message' => 'Bin added successfully', 'bin' => $bin]);
    }

    // Delete Bin (with all waste levels)
    public function destroy($id)
    {
        $bin = Bin::findOrFail($id);
        $bin->wasteLevels()->delete(); // delete all waste levels
        $bin->delete(); // delete bin itself

        return response()->json(['message' => 'Bin and all its data deleted successfully']);
    }
}

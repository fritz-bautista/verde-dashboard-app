<?php

namespace App\Http\Controllers;

use App\Models\RankingManager;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RankingController extends Controller
{
    // Show ranking manager page
    public function index()
    {
        $current = RankingManager::where('is_active', true)->first();
        $history = RankingManager::where('is_active', false)
            ->orderBy('started_at', 'desc')
            ->get();

        return view('admin.ranking-manager', compact('current', 'history'));
    }

    // Start a new semester ranking
    public function start(Request $request)
    {
        $request->validate([
            'semester_name' => 'required|string|max:255',
        ]);

        // Stop any existing active ranking first
        RankingManager::where('is_active', true)->update([
            'is_active' => false,
            'stopped_at' => Carbon::now(),
        ]);

        // Create a new active ranking
        RankingManager::create([
            'semester_name' => $request->semester_name,
            'is_active' => true,
            'started_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'New semester ranking started successfully!');
    }

    // Stop the current active ranking
    public function stop($id)
    {
        $ranking = RankingManager::findOrFail($id);
        $ranking->update([
            'is_active' => false,
            'stopped_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('warning', 'The active ranking has been stopped. All rankings are now frozen.');
    }
}

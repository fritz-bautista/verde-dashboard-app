<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\College;
use App\Models\Ranking;
use App\Models\RankingManager;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;



class CollegeRankingController extends Controller
{
    // Show the merged College + Ranking Manager page
    public function index()
    {
        $colleges = College::withCount('students')->get(); // College list with student count
        $rankings = Ranking::with('college')->orderByDesc('score')->get(); // Rankings
        $current = RankingManager::where('is_active', true)->first(); // Current active ranking
        $history = RankingManager::orderByDesc('created_at')->get(); // Ranking history

        return view('admin.college-ranking-manager', compact(
            'colleges',
            'rankings',
            'current',
            'history'
        ));
    }

    // College CRUD actions
    public function storeCollege(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'dean' => 'nullable|string|max:255',
            'student_count' => 'nullable|integer|min:0',
        ]);

        College::create($request->only(['name', 'dean', 'student_count']));
        return back()->with('success', 'College added successfully!');
    }

    public function updateCollege(Request $request, $id)
    {
        $college = College::findOrFail($id);
        $college->update($request->only(['name', 'dean', 'student_count']));
        return back()->with('success', 'College updated successfully!');
    }

    public function destroyCollege($id)
    {
        College::findOrFail($id)->delete();
        return back()->with('success', 'College deleted successfully!');
    }

    // Ranking actions (supports custom start and stop dates)
    public function toggleRanking(Request $request)
    {
        $action = $request->action;

        if ($action === 'start') {
            // Validate inputs
            $request->validate([
                'semester_name' => 'required|string|max:255',
                'started_at' => 'nullable|date',
                'stopped_at' => 'nullable|date|after_or_equal:started_at',
            ]);

            // Stop any existing active ranking first
            RankingManager::where('is_active', true)->update([
                'is_active' => false,
                'stopped_at' => $request->started_at ?? Carbon::now(), // stop old ranking when new starts
            ]);

            // Start new ranking
            RankingManager::create([
                'semester_name' => $request->semester_name,
                'is_active' => true,
                'started_at' => $request->started_at ?? Carbon::now(),
                'stopped_at' => $request->stopped_at ?? null,
            ]);

            return response()->json(['success' => true]);
        }

        if ($action === 'stop') {
            $request->validate([
                'stopped_at' => 'nullable|date',
            ]);

            $current = RankingManager::where('is_active', true)->first();
            if ($current) {
                $current->update([
                    'is_active' => false,
                    'stopped_at' => $request->stopped_at ?? Carbon::now(),
                ]);
            }
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action']);
    }
    public function getSemesterPoints($id)
    {
        $ranking = RankingManager::findOrFail($id); // make sure $id exists
        $points = $ranking->rankings()->with('college')->get()->map(function ($r) {
            return [
                'college_name' => $r->college->name,
                'score' => $r->score,
            ];
        });

        return response()->json(['success' => true, 'points' => $points]);
    }


    public function downloadSemesterPdf($id)
    {
        $ranking = RankingManager::findOrFail($id);
        $rankings = $ranking->rankings()->with('college')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.semester-pdf', compact('ranking', 'rankings'));
        return $pdf->download("Semester_{$ranking->semester_name}_Ranking.pdf");
    }


}

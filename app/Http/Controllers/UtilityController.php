<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utility;
use App\Models\Level;
use App\Models\UtilityAssignment;
use Carbon\Carbon;

class UtilityController extends Controller
{
    public function index()
    {
        $utilities = Utility::all();
        $levels = Level::all();
        $assignments = UtilityAssignment::with(['utility', 'level'])->latest()->get();

        return view('main.utility_manager', compact('utilities', 'levels', 'assignments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
        ]);

        Utility::create([
            'name' => $request->name,
            'contact' => $request->contact,
            'status' => 'available',
        ]);

        return back()->with('success', 'Utility staff added successfully!');
    }

    public function assign(Request $request)
    {
        $request->validate([
            'utility_id' => 'required|exists:utilities,id',
            'level_id' => 'required|exists:levels,id',
        ]);

        UtilityAssignment::create([
            'utility_id' => $request->utility_id,
            'level_id' => $request->level_id,
            'assigned_date' => Carbon::now(),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Utility staff assigned successfully!');
    }

    public function updateStatus($id)
    {
        $assignment = UtilityAssignment::findOrFail($id);
        $assignment->status = $assignment->status === 'completed' ? 'pending' : 'completed';
        $assignment->save();

        return back()->with('success', 'Assignment status updated!');
    }

    public function destroy($id)
    {
        $utility = Utility::findOrFail($id);
        $utility->delete();

        return back()->with('success', 'Utility staff removed successfully!');
    }
}

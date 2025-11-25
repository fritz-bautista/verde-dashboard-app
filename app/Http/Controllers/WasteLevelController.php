<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WasteLevel;

class WasteLevelController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'bin_id' => 'required|exists:bins,id',
            'weight' => 'required|numeric',
            'level' => 'nullable|numeric',
            'collected' => 'nullable|boolean',
        ]);

        WasteLevel::create([
            'bin_id' => $request->bin_id,
            'weight' => $request->weight,
            'level' => $request->level, // fill level
        ]);


        if ($request->boolean('collected')) {
            \Log::info("🛑 Bin {$request->bin_id} was collected at " . now()); 
        }

        return response()->json(['message' => 'Weight recorded successfully']);
    }
}

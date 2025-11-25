<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Ranking;
use Illuminate\Http\Request;

class CollegeController extends Controller
{
    public function index()
    {
        $colleges = College::all();
        $rankings = Ranking::with('college')->orderByDesc('score')->get();
        return view('main.college_manager', compact('colleges', 'rankings'));
    }

    public function store(Request $request)
    {
        College::create($request->only(['name', 'dean', 'student_count']));
        return back()->with('success', 'College added successfully!');
    }

    public function update(Request $request, $id)
    {
        College::findOrFail($id)->update($request->only(['name', 'dean', 'student_count']));
        return back()->with('success', 'College updated successfully!');
    }

    public function destroy($id)
    {
        College::findOrFail($id)->delete();
        return back()->with('success', 'College deleted.');
    }
}

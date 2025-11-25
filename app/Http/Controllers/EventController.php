<?php

namespace App\Http\Controllers;

use App\Models\Events;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Events::latest()->get();
        return view('main.events', compact('events'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'floor' => 'nullable|integer',
            'attendees' => 'required|integer',
        ]);

        Events::create($request->all());

        return redirect()->back()->with('success', 'Event added successfully.');
    }

}

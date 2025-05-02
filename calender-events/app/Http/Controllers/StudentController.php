<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event; // Make sure to import your Event model

class StudentController extends Controller
{
    // Existing methods
    public function index()
    {
        return view('student.index');
    }

    public function create()
    {
        return view('student.create');
    }

    public function store(Request $request)
    {
        // Your existing store logic
        return redirect('/student')->with('success', 'Event created successfully!');
    }

    // Add this new method for fetching events
    public function getEvents()
    {
        $events = Event::all()->map(function ($event) {
            return [
                'title' => $event->title,
                'start' => $event->start_date,
                'end' => $event->end_date,
                'description' => $event->description,
                // Add other event properties you need
            ];
        });

        return response()->json($events);
    }

    // Calendar view method
    public function calendar()
    {
        return view('student.calendar');
    }
}

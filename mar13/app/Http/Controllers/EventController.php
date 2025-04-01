<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::orderBy('date', 'asc')
                      ->where('date', '>=', now())
                      ->get();
        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.events.create');
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'description' => 'nullable|string'
        ]);
    
        // Capitalize the first letter of each word in the title
        $validated['title'] = ucwords(strtolower($validated['title']));
    
        Event::create($validated);
    
        return redirect()->back()
                        ->with('success', 'Event created successfully');
    }


    public function show(string $id)
    {
        $event = Event::findOrFail($id);
        return view('admin.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $event = Event::findOrFail($id);
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'description' => 'nullable|string'
        ]);

        $event->update($validated);

        return redirect()->back()
                        ->with('success', 'Event updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyEvent(Event $event)
    {
        try {
            $event->delete();
            return redirect()->back()->with('success', 'Event deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete event');
        }
    }
}

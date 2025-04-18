<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    //Create Event 
    public function createEvent(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required',
            'location' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|mimes:jpg,png,svg,pdf|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event = Event::create($validated);

        return response()->json(['message' => 'Event created successfully.', 'event' => $event], 201);
    }

    // READ (All Events)
    public function fetchEvents()
    {
        $events = Event::all();
        return response()->json($events, 200);
    }

    // READ (Single Event)
    public function getEvent($id)
    {
        $event = Event::findOrFail($id);
        return response()->json($event, 200);
    }

    // UPDATE
    public function updateEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'sometimes|required|date',
            'time' => 'sometimes|required',
            'location' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($event->image) Storage::disk('public')->delete($event->image);
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);
        return response()->json(['message' => 'Event updated successfully.', 'event' => $event], 200);
    }

    // DELETE
    public function deleteEvent($id)
    {
        $event = Event::findOrFail($id);
        if ($event->image) Storage::disk('public')->delete($event->image);
        $event->delete();
        return response()->json(['message' => 'Event deleted successfully.'], 200);
    }
}

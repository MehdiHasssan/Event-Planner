<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class EventController extends Controller
{
    // CREATE
    public function createEvent(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'time' => 'required',
                'location' => 'required|string|max:255',
                'category' => 'required|string',
                'price' => 'required|numeric',
                'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->getClientOriginalExtension();
                $request->image->move(public_path('uploads/images/'), $imageName);
                $validated['image'] = $imageName;
            }

            $event = Event::create($validated);

            return response()->json([
                'message' => 'Event created successfully.',
                'event' => $event
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the event.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // READ ALL
    public function fetchEvents()
    {
        try {
            $events = Event::all()->map(function ($event) {
                if ($event->image) {
                    $event->image = asset("uploads/images/{$event->image}");
                }
                return $event;
            });

            return response()->json($events, 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch events.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // READ SINGLE
    public function getEvent($id)
    {
        try {
            $event = Event::findOrFail($id);
            if ($event->image) {
                $event->image = asset("uploads/images/{$event->image}");
            }

            return response()->json($event, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Event not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving event.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // UPDATE
    public function updateEvent(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'sometimes|required|date',
                'time' => 'sometimes|required',
                'location' => 'sometimes|required|string|max:255',
                'category' => 'sometimes|required|string',
                'price' => 'sometimes|required|numeric',
                'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            ]);

            if ($request->hasFile('image')) {
                if ($event->image) {
                    $oldImagePath = public_path("uploads/images/{$event->image}");
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }
                }

                $imageName = time() . '.' . $request->image->getClientOriginalExtension();
                $request->image->move(public_path('uploads/images/'), $imageName);
                $validated['image'] = $imageName;
            }

            $event->update($validated);

            return response()->json([
                'message' => 'Event updated successfully.',
                'event' => $event
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Event not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the event.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // DELETE
    public function deleteEvent($id)
    {
        try {
            $event = Event::findOrFail($id);

            if ($event->image) {
                $imagePath = public_path("uploads/images/{$event->image}");
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }
            }

            $event->delete();

            return response()->json([
                'message' => 'Event deleted successfully.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Event not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the event.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

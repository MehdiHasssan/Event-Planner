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
                'message' => 'Event not found with this ID.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // UPDATE
    public function updateEvent(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);

            // Log raw request data for debugging
            \Log::info('UpdateEvent Raw Request:', [
                'method' => $request->method(),
                'headers' => $request->headers->all(),
                'files' => $request->allFiles(),
                'inputs' => $request->all(),
            ]);

            // Validate non-file fields
            $validated = $request->validate([
                'title' => 'sometimes|nullable|string|max:255',
                'description' => 'nullable|string',
                'date' => 'sometimes|nullable|date',
                'time' => 'sometimes|nullable',
                'location' => 'sometimes|nullable|string|max:255',
                'category' => 'sometimes|nullable|string',
                'price' => 'sometimes|nullable|numeric',
                'image' => 'sometimes|nullable|string', // For filenames
                'image_base64' => 'sometimes|nullable|string', // For base64-encoded images
            ]);

            // Debug logging
            \Log::info('UpdateEvent Request Data:', [
                'image_input' => $request->input('image'),
                'has_file_image' => $request->hasFile('image'),
                'file_image' => $request->file('image') ? $request->file('image')->getClientOriginalName() : null,
                'image_base64' => $request->has('image_base64'),
                'all_inputs' => $request->all(),
                'files_count' => count($request->allFiles()),
            ]);

            // Handle file upload
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $imageFile = $request->file('image');
                \Log::info('Processing file upload:', [
                    'filename' => $imageFile->getClientOriginalName(),
                    'size' => $imageFile->getSize(),
                    'mime' => $imageFile->getMimeType(),
                    'path' => $imageFile->getPathname(),
                ]);

                // Validate file
                $request->validate([
                    'image' => 'image|mimes:jpg,png,jpeg|max:2048',
                ]);

                $imageName = time() . '.' . $imageFile->getClientOriginalExtension();
                $imageFile->move(public_path('uploads/images/'), $imageName);

                // Delete old image if exists
                if ($event->image) {
                    $oldImagePath = public_path("uploads/images/{$event->image}");
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                        \Log::info('Deleted old image:', ['path' => $oldImagePath]);
                    }
                }

                $validated['image'] = $imageName;
            } elseif ($request->has('image_base64') && !empty($request->input('image_base64'))) {
                // Handle base64-encoded image
                $imageData = $request->input('image_base64');
                preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches);
                if (!isset($matches[1])) {
                    throw new Exception('Invalid base64 image format.');
                }
                $imageType = $matches[1];

                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    throw new Exception('Failed to decode base64 image.');
                }

                $imageName = time() . '.' . $imageType;
                $imagePath = public_path('uploads/images/' . $imageName);
                file_put_contents($imagePath, $imageData);

                // Delete old image if exists
                if ($event->image) {
                    $oldImagePath = public_path("uploads/images/{$event->image}");
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                        \Log::info('Deleted old image:', ['path' => $oldImagePath]);
                    }
                }

                $validated['image'] = $imageName;
            } elseif ($request->has('image') && !empty($request->input('image'))) {
                // Handle image filename
                $imageName = $request->input('image');
                $imagePath = public_path("uploads/images/{$imageName}");
                \Log::info('Checking existing image:', [
                    'imageName' => $imageName,
                    'exists' => File::exists($imagePath),
                ]);

                if (!File::exists($imagePath)) {
                    \Log::warning('Image file does not exist, proceeding with update:', ['imageName' => $imageName]);
                }

                // Delete old image if different
                if ($event->image && $event->image !== $imageName) {
                    $oldImagePath = public_path("uploads/images/{$event->image}");
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                        \Log::info('Deleted old image:', ['path' => $oldImagePath]);
                    }
                }

                $validated['image'] = $imageName;
            } else {
                \Log::info('No image update requested; retaining existing image:', ['current_image' => $event->image]);
            }

            // Update the event
            $event->update($validated);

            // Append full image URL to response
            if ($event->image) {
                $event->image = asset("uploads/images/{$event->image}");
            }

            \Log::info('Updated Event:', [
                'event_id' => $event->id,
                'image' => $event->image,
                'database_image' => $event->getOriginal('image'),
            ]);

            return response()->json([
                'message' => 'Event updated successfully.',
                'event' => $event
            ], 200);
        } catch (ModelNotFoundException $e) {
            \Log::error('Event not found:', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Event not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (ValidationException $e) {
            \Log::error('Validation failed:', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            \Log::error('UpdateEvent Error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'An error occurred while updating the event.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Other methods (createEvent, fetchEvents, getEvent, deleteEvent) remain unchanged


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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventGallery;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class EventGalleryController extends Controller
{
    // Create gallery with single/multiple images
    public function createGallery(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'images' => 'required',
            ]);

            // Handle single/multiple images
            $images = $request->file('images');
            if (!is_array($images)) {
                $images = [$images];
            }

            // Validate images
            foreach ($images as $image) {
                Validator::make(['image' => $image], [
                    'image' => 'image|mimes:jpg,png,jpeg|max:2048'
                ])->validate();
            }

            // Process and store images
            $imageObjects = [];
            foreach ($images as $index => $image) {
                $imageName = time() . '_' . $index . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/gallery/'), $imageName);

                $imageObjects[] = [
                    'path' => 'uploads/gallery/' . $imageName,
                    'filename' => $image->getClientOriginalName()
                ];
            }

            // Create gallery entry
            $gallery = EventGallery::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'images' => json_encode($imageObjects),
            ]);

            // Add full URLs to response
            $gallery->images = $this->formatImageUrls($imageObjects);

            return response()->json([
                'message' => 'Gallery created successfully',
                'gallery' => $gallery
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // fetch all images
    public function fetchGalleries()
    {
        try {
            // $galleries = EventGallery::lastest()->get();
            $galleries = EventGallery::latest()->get();

            // Transform images to include full URLs
            $galleries->each(function ($gallery) {
                $images = is_string($gallery->images) ? json_decode($gallery->images, true) : $gallery->images;


                if (!is_array($images)) {
                    $gallery->images = [];
                    return;
                }

                $gallery->images = collect($images)->map(function ($image) {
                    if (!is_array($image) || !isset($image['path']) || !isset($image['filename'])) {
                        return null;
                    }
                    return [
                        'path' => asset($image['path']),
                        'filename' => $image['filename']
                    ];
                })->filter()->values();
            });

            return response()->json($galleries);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Get single gallery
    public function showGallery($id)
    {
        try {
            $gallery = EventGallery::findOrFail($id);
            // $images = json_decode($gallery->images, true);
            $images = is_string($gallery->images) ? json_decode($gallery->images, true) : $gallery->images;

            if (!is_array($images)) {
                $gallery->images = [];
            } else {
                $gallery->images = collect($images)->map(function ($image) {
                    if (!is_array($image) || !isset($image['path']) || !isset($image['filename'])) {
                        return null;
                    }
                    return [
                        'path' => asset($image['path']),
                        'filename' => $image['filename']
                    ];
                })->filter()->values();
            }

            return response()->json($gallery);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Gallery not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // Update gallery
    public function updateGallery(Request $request, $id)
    {
        try {
            $gallery = EventGallery::findOrFail($id);

            $validatedData = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'images' => 'sometimes',
            ]);

            // Handle image updates
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                if (!is_array($images)) {
                    $images = [$images];
                }

                // Validate new images
                foreach ($images as $image) {
                    Validator::make(['image' => $image], [
                        'image' => 'image|mimes:jpg,png,jpeg|max:2048'
                    ])->validate();
                }

                // Delete old images
                foreach (json_decode($gallery->images, true) as $oldImage) {
                    @unlink(public_path($oldImage['path']));
                }

                // Store new images
                $imageObjects = [];
                foreach ($images as $index => $image) {
                    $imageName = time() . '_' . $index . '_' . $image->getClientOriginalName();
                    $image->move(public_path('uploads/gallery/'), $imageName);

                    $imageObjects[] = [
                        'path' => 'uploads/gallery/' . $imageName,
                        'filename' => $image->getClientOriginalName()
                    ];
                }

                $validatedData['images'] = json_encode($imageObjects);
            }

            $gallery->update($validatedData);
            
            // Add full URLs to response
            $gallery->images = $this->formatImageUrls(json_decode($gallery->images, true));

            return response()->json([
                'message' => 'Gallery updated successfully',
                'gallery' => $gallery
            ]);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Gallery not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Delete gallery
    public function deleteGallery($id)
        {
            try {
                $gallery = EventGallery::findOrFail($id);

                // Delete associated images (no need for json_decode)
                foreach ($gallery->images as $image) {
                    @unlink(public_path($image['path']));
                }

                $gallery->delete();

                return response()->json([
                    'message' => 'Gallery deleted successfully'
                ]);

            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Gallery not found'], 404);
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

    // Helper method to format image URLs
    private function formatImageUrls($images)
    {
        return collect($images)->map(function ($image) {
            return [
                'path' => asset($image['path']),
                'filename' => $image['filename']
            ];
        });
    }
}
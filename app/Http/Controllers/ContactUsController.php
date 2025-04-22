<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactUs;

class ContactUsController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Validate incoming data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'phone' => 'string|max:11',
                'message' => 'required|string',
            ]);

            // Create a new ContactUs record
            $contact = ContactUs::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'message' => $validatedData['message'],
            ]);

            return response()->json([
                'message' => 'Thank you for contacting us. We will get back to you soon.',
                'contact' => $contact
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred while processing your request.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Optional: You can add methods for viewing all inquiries or specific ones
    public function index()
    {
        try {
            $contacts = ContactUs::all();  // Retrieve all contact submissions
            return response()->json([
                'contacts' => $contacts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving contacts.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

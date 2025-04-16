<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'username' => 'required|string|max:255|unique:users,username',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            Auth::login($user);

            return response()->json([
                'message' => 'Registration successful.',
                'user' => $user
            ], 201);
        } catch (ValidationException $e) {
            // Return only the first error as an object
            $firstErrorField = array_key_first($e->errors());
            return response()->json([
                'message' => 'Validation failed.',
                'error' => [
                    $firstErrorField => $e->errors()[$firstErrorField][0]
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred during registration.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string|min:8',
            ]);

            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid email or password.'
                ], 401);
            }

            return response()->json([
                'message' => 'Login successful.',
                'user' => Auth::user()
            ], 200);
        } catch (ValidationException $e) {
            $firstErrorField = array_key_first($e->errors());
            return response()->json([
                'message' => 'Validation failed.',
                'error' => [
                    $firstErrorField => $e->errors()[$firstErrorField][0]
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred during login.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

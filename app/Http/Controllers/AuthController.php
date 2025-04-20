<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

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

            // Generate JWT
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Registration successful.',
                'user' => $user,
                'token' => $token
            ], 201);
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

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid email or password.'
                ], 401);
            }

            return response()->json([
                'message' => 'Login successful.',
                'user' => Auth::user(),
                'token' => $token
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
    //logout
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'message' => 'Logout successful.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while logging out.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}

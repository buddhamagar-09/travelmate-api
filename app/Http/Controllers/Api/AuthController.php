<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // LOGIN
    public function login(Request $request, user $user)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        if ($user->status === 'inactive') {
            // Auth::logout(); // Optional but recommended

            return response()->json([
                'success' => false,
                'message' => 'Your account has been blocked.',
            ], 403);
        }

        // Delete previous tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('travelmate')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
    // CURRENT USER
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
//AuthController handles user authentication for the API.
// It provides methods for logging in, logging out,
// and retrieving the currently authenticated user's information. The login method validates the user's credentials,
// generates a new API token, and returns it along with the user data. The logout method revokes the current access token, 
//effectively logging the user out. The user method returns the authenticated user's details.
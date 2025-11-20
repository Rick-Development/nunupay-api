<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Ensure you have the User model imported
use Laravel\Sanctum\HasApiTokens;

class TokenController extends Controller
{
    private $appName;

    public function __construct()
    {
        $this->appName = env('APP_NAME', 'Laravel'); // Fallback to 'Laravel' if APP_NAME is not set
    }

    /**
     * Generate a new API token for the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateToken(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to log the user in
        if (Auth::attempt($request->only('email', 'password'))) {
            // Get the authenticated user
            $user = Auth::user();

            // Generate a new token
            $token = $user->createToken($this->appName)->plainTextToken;

            // Return the token and user info as a response
            return response()->json([
                'token' => $token,
                'user' => $user // Optionally return user info
            ], 200);
        }

        // If authentication fails, return an error response
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
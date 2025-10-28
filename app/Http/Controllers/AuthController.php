<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email:rfc|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return $this->respond(['user' => $user], 'User registered successfully', 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email:rfc',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($credentials)) {
            return $this->respondError('Invalid credentials', [], 401);
        }

        /** @var \App\Models\User $user */
        $user  = Auth::user();
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $user->createToken('kusha_token');
        return $this->respond(['token' => $token->plainTextToken, 'user' => $user]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return $this->respond('Logged out');
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email:rfc|exists:users,email',
        ]);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $validated
        );

        if ($status !== \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            return $this->respondError('Unable to send reset link', [], 500);
        }

        return $this->respond('Password reset link sent to your email');
    }
}

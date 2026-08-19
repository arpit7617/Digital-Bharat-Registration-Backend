<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    /**
     * Handle Google Sign-In / Sign-Up.
     *
     * Scenarios:
     *  1. Existing user with completed payment → login (return user data).
     *  2. Existing user with pending payment → return payment_pending status.
     *  3. New user → return needs_registration so Flutter can open the registration form.
     */
    public function googleAuth(Request $request)
    {
        $request->validate([
            'email'     => 'required|email',
            'google_id' => 'required|string',
            'name'      => 'nullable|string',
            'avatar'    => 'nullable|string',
        ]);

        $email    = strtolower(trim($request->email));
        $googleId = trim($request->google_id);

        try {
            // Look up by google_id first, then by email
            $user = Registration::where('google_id', $googleId)->first()
                ?? Registration::where('email', $email)->first();

            if ($user) {
                // Link google_id if not linked yet
                if (!$user->google_id) {
                    $user->google_id = $googleId;
                    if ($request->avatar && !$user->avatar) {
                        $user->avatar = $request->avatar;
                    }
                    $user->save();
                }

                $isPaid = ($user->payment_status === 'completed') || (bool) $user->payment_acknowledged;

                if (!$isPaid) {
                    return response()->json([
                        'status'          => 'payment_pending',
                        'message'         => 'Payment pending. Please complete registration payment.',
                        'payment_pending' => true,
                        'user'            => $user,
                    ], 403);
                }

                return response()->json([
                    'status' => 'success',
                    'user'   => $user,
                    'is_new' => false,
                ], 200);
            }

            // New user — tell Flutter to show registration form with pre-filled data
            return response()->json([
                'status'      => 'needs_registration',
                'message'     => 'Please complete your registration profile.',
                'google_user' => [
                    'email'     => $email,
                    'name'      => $request->name ?? '',
                    'google_id' => $googleId,
                    'avatar'    => $request->avatar ?? '',
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Google Auth error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Server error during Google authentication.',
            ], 500);
        }
    }
}

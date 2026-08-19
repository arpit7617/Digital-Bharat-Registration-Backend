<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Send OTP for password reset (Strictly via Registered Email ID)
     */
    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'nullable|string',
            'identifier' => 'nullable|string',
        ]);

        $rawInput = trim((string)($request->email ?? $request->identifier));
        $email = strtolower($rawInput);

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please enter a valid registered email address.'
            ], 422);
        }

        // 1. Search for user specifically by email
        $user = Registration::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No account found with this email address.'
            ], 404);
        }

        // 2. Generate 6-digit OTP
        $otp = (string) mt_rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(15);

        // 3. Clear existing OTPs for this email and save new OTP
        DB::table('password_reset_otps')->where('identifier', $email)->delete();
        DB::table('password_reset_otps')->insert([
            'identifier' => $email,
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 4. Send Email
        $mailSent = false;
        try {
            Mail::to($user->email)->send(new PasswordResetMail($user->name, $otp));
            $mailSent = true;
        } catch (\Exception $e) {
            Log::error("PasswordResetMail failed for {$user->email}: " . $e->getMessage());
        }

        $parts = explode('@', $user->email);
        $namePart = $parts[0] ?? '';
        $domain = $parts[1] ?? '';
        $maskedName = strlen($namePart) <= 2 ? substr($namePart, 0, 1) . '***' : substr($namePart, 0, 3) . '***';
        $maskedEmail = $maskedName . '@' . $domain;

        $responsePayload = [
            'status' => 'success',
            'message' => $mailSent
                ? "Verification code sent to your registered email ({$maskedEmail})."
                : "Verification code generated successfully.",
            'email' => $user->email,
            'identifier' => $user->email,
        ];

        // Include OTP in response payload during debug mode for local testing ease
        if (config('app.debug')) {
            $responsePayload['debug_otp'] = $otp;
        }

        return response()->json($responsePayload, 200);
    }

    /**
     * Verify OTP
     */
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'nullable|string',
            'identifier' => 'nullable|string',
            'otp' => 'required|string',
        ]);

        $rawInput = trim((string)($request->email ?? $request->identifier));
        $email = strtolower($rawInput);
        $otp = trim($request->otp);

        $record = DB::table('password_reset_otps')
            ->where('identifier', $email)
            ->where('otp', $otp)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired verification code. Please request a new code.'
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Verification code verified successfully.'
        ], 200);
    }

    /**
     * Reset Password using OTP (Email verification required)
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'nullable|string',
            'identifier' => 'nullable|string',
            'otp' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $rawInput = trim((string)($request->email ?? $request->identifier));
        $email = strtolower($rawInput);
        $otp = trim($request->otp);

        // 1. Verify OTP record
        $record = DB::table('password_reset_otps')
            ->where('identifier', $email)
            ->where('otp', $otp)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired verification code session. Please request a new code.'
            ], 422);
        }

        // 2. Find User by Email
        $user = Registration::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User account not found.'
            ], 404);
        }

        // 3. Update Password
        $user->password = Hash::make($request->password);
        $user->save();

        // 4. Delete used OTP
        DB::table('password_reset_otps')->where('identifier', $email)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset successfully! You can now log in with your new password.'
        ], 200);
    }

    /**
     * Change Password (for logged in users)
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        $user = Registration::find($request->id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password does not match.'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully!'
        ], 200);
    }
}

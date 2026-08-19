<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    use RegisterReferralWalletCredit;

    public function register(Request $request)
    {
        // 1. Basic Format Validation
        $request->validate([
            'name' => 'required|string',
            'mobile' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'category' => 'required|string',
            'pincode' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
        ]);

        // 2. Sanitize and normalize inputs
        $email = strtolower(trim($request->email));
        $mobile = trim($request->mobile);

        // 3. Separate duplicate checks to avoid SQL key collisions
        $existingEmail = Registration::where('email', $email)->first();
        $existingMobile = Registration::where('mobile', $mobile)->first();

        if ($existingEmail && $existingMobile && $existingEmail->id !== $existingMobile->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided email and mobile number belong to two different registered accounts.'
            ], 422);
        }

        $existing = $existingEmail ?? $existingMobile;

        if ($existing) {
            $isPaid = ($existing->payment_status === 'completed') || (bool)$existing->payment_acknowledged;
            if ($isPaid) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'An active paid account with this email or mobile already exists. Please log in.'
                ], 422);
            }

            // Unpaid pending account hijacking prevention:
            // Verify password if user tries to update an existing pending account
            if (!Hash::check($request->password, $existing->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'An account with this email or mobile is pending payment. Please log in with your existing password to complete registration.'
                ], 422);
            }
        }

        try {
            $data = $request->all();
            $data['email'] = $email;
            $data['mobile'] = $mobile;
            $data['password'] = Hash::make($request->password);
            $data['payment_status'] = $data['payment_status'] ?? 'pending';
            $data['payment_acknowledged'] = ($data['payment_status'] === 'completed');
            if (!empty($data['terms_accepted']) && empty($data['terms_accepted_at'])) {
                $data['terms_accepted_at'] = now();
            }

            if ($existing) {
                $existing->update($data);
                $user = $existing;
            } else {
                $user = Registration::create($data);
            }

            // Send Registration Success Email ONLY for Portal users if payment_status is explicitly 'completed'
            // (WordPress registrants receive their credentials email from WordPress)
            try {
                if (!empty($user->email) && $user->payment_status === 'completed' && ($user->registration_source ?? '') !== 'wordpress') {
                    $adminEmail = env('ADMIN_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS', 'support@digitalindiaregistration.com'));
                    \Illuminate\Support\Facades\Mail::to($user->email)
                        ->bcc($adminEmail)
                        ->send(new \App\Mail\RegistrationSuccessMail($user));
                }
            } catch (\Exception $mailEx) {
                Log::error("Failed to send registration email to {$user->email}: " . $mailEx->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Registration Successful',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $loginId = trim((string)$request->mobile);
        $loginEmail = strtolower($loginId);

        // Search by mobile or email
        $user = Registration::where('mobile', $loginId)
            ->orWhere('email', $loginEmail)
            ->first();

        // Check if user exists and verify password
        if ($user && Hash::check($request->password, $user->password)) {
            // Check if payment has been completed/acknowledged
            $isPaid = ($user->payment_status === 'completed') || (bool)$user->payment_acknowledged;

            if (!$isPaid) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment pending. Please complete registration payment to login.',
                    'payment_pending' => true,
                    'user' => $user
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'user' => $user
            ], status: 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function getAllRegistrations()
    {
        try {
            $registrations = Registration::select(
                'id', 'name', 'mobile', 'email', 'category', 'pincode', 'city', 'state',
                'company_name', 'college_name', 'bank_name', 'payment_status', 'payment_acknowledged',
                'custom_id', 'created_at'
            )->orderBy('created_at', 'desc')->get();
            return response()->json($registrations, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function showUser($id)
    {
        try {
            $user = Registration::find($id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'user' => $user,
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request, $id)
    {
        try {
            $user = Registration::findOrFail($id);
            $user->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function confirmPayment(Request $request)
    {
        $registrationId = $request->input('registration_id');
        $email = strtolower(trim((string)$request->input('email')));
        $mobile = trim((string)$request->input('mobile'));
        $paymentId = $request->input('payment_id', $request->input('transaction_id'));

        return DB::transaction(function () use ($registrationId, $email, $mobile, $paymentId, $request) {
            $userQuery = Registration::query();

            if ($registrationId) {
                $userQuery->where('id', $registrationId);
            } elseif (!empty($email)) {
                $userQuery->where('email', $email);
            } elseif (!empty($mobile)) {
                $userQuery->where('mobile', $mobile);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration identifier is required'
                ], 422);
            }

            $user = $userQuery->lockForUpdate()->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration record not found'
                ], 404);
            }

            $wasAlreadyPaid = ($user->payment_status === 'completed') && (bool)$user->payment_acknowledged;

            $user->update([
                'payment_acknowledged' => true,
                'payment_status' => 'completed',
                'payment_id' => $paymentId ?? $user->payment_id,
            ]);

            if (!$wasAlreadyPaid) {
                // Dispatch welcome email
                if (!empty($user->email)) {
                    try {
                        $adminEmail = env('ADMIN_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS', 'support@digitalindiaregistration.com'));
                        \Illuminate\Support\Facades\Mail::to($user->email)
                            ->bcc($adminEmail)
                            ->send(new \App\Mail\RegistrationSuccessMail($user));
                    } catch (\Exception $mailEx) {
                        Log::error("Failed to send registration email to {$user->email}: " . $mailEx->getMessage());
                    }
                }

                // Credit partner referral wallet ONLY upon completed payment
                try {
                    $this->creditPartnerReferralWallet($request, $user);
                } catch (\Exception $ex) {
                    Log::error("Failed to credit partner referral wallet on confirmPayment: " . $ex->getMessage());
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment confirmed successfully',
                'user' => $user
            ], 200);
        });
    }
}


<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    use RegisterReferralWalletCredit;
    public function register(Request $request)
    {
        // 1. Core Validation
        $request->validate([
            'name' => 'required|string',
            'mobile' => 'required|string|unique:registrations,mobile',
            'email' => 'required|email|unique:registrations,email', // Validates email format and uniqueness
            'password' => 'required|string|min:6',
            'category' => 'required|string',
            'pincode' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
        ]);

        // 2. Automated Save
        // Because of our $fillable array in the Model, this handles all dynamic fields
        try {

            // 2. Prepare Data
            $data = $request->all();

            // 3. Encrypt the password so it isn't saved as plain text
            $data['password'] = Hash::make($request->password);

            // 4. Automated Save
            $user = Registration::create($data);

            // 5. Credit partner referral wallet if referee registered using referred_partner_code
            try {
                $this->creditPartnerReferralWallet($request, $user);
            } catch (\Exception $ex) {
                \Illuminate\Support\Facades\Log::error("Failed to credit partner referral wallet: " . $ex->getMessage());
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
        // Search by mobile or email
        $user = Registration::where('mobile', $request->mobile)
            ->orWhere('email', $request->mobile)
            ->first();

        // Check if user exists and verify the encrypted password
        if ($user && Hash::check($request->password, $user->password)) {
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
            $registrations = Registration::orderBy('created_at', 'desc')->get();
            return response()->json($registrations, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
}

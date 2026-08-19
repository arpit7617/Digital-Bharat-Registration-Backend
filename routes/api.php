<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ValidatePartnerCodeController;
use App\Http\Controllers\PartnerWalletController;
use App\Http\Controllers\SupportMessageController;

use App\Http\Controllers\CashfreeWebhookController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\GoogleAuthController;

// Google OAuth Authentication
Route::post('/auth/google', [GoogleAuthController::class, 'googleAuth']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Automated Cashfree Payment Gateway Webhook Endpoint
Route::post('/cashfree-webhook', [CashfreeWebhookController::class, 'handleWebhook']);
Route::post('/webhooks/cashfree', [CashfreeWebhookController::class, 'handleWebhook']);

// Password Reset Endpoints (with rate limiting protection)
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetOtp'])->middleware('throttle:5,1');
Route::post('/verify-reset-otp', [PasswordResetController::class, 'verifyResetOtp'])->middleware('throttle:10,1');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:5,1');
Route::post('/change-password', [PasswordResetController::class, 'changePassword'])->middleware('throttle:5,1');

// Registration & Payment Endpoints
Route::post('/register', [RegistrationController::class, 'register']);
Route::post('/confirm-payment', [RegistrationController::class, 'confirmPayment']);
Route::put('/profile/{id}', [RegistrationController::class, 'updateProfile']);
Route::post('/login', [RegistrationController::class, 'login']);

Route::get('/support-messages/{userId}', [SupportMessageController::class, 'index']);
Route::post('/support-messages', [SupportMessageController::class, 'store']);
Route::post('/support-messages/admin-reply', [SupportMessageController::class, 'adminReply']);

Route::post('/validate-partner-code', [ValidatePartnerCodeController::class, 'validateCode']);
Route::get('/validate-partner-code/{code}', [ValidatePartnerCodeController::class, 'validateGet']);
Route::get('/partner-wallet', [PartnerWalletController::class, 'show']);
Route::post('/partner-wallet/credit', [PartnerWalletController::class, 'credit']);

Route::post('/save-service', [ServiceController::class, 'saveData']);
Route::post('/leads/update-status', [LoanController::class, 'updateStatus']);

Route::get('/leads', [LoanController::class, 'getAllLeads']);
Route::get('/my-loans', [LoanController::class, 'getUserLoans']);
Route::get('/leads/my-accepted-leads', [LoanController::class, 'getMyAcceptedLeads']);
Route::post('/leads/delete', [LoanController::class, 'deleteLead']);
Route::get('/dashboard-stats', [LoanController::class, 'getDashboardStats']);
Route::get('/jobs', [LoanController::class, 'getJobs']);
Route::get('/business/jobs', [LoanController::class, 'getBusinessJobs']);
Route::get('/business/applicants', [LoanController::class, 'getBusinessApplicants']);
Route::get('/my-job-applications', [LoanController::class, 'getMyJobApplications']);
Route::get('/market/crops', [LoanController::class, 'getMarketCrops']);
Route::get('/registrations', [RegistrationController::class, 'getAllRegistrations']);
Route::get('/users', [RegistrationController::class, 'getAllRegistrations']);
Route::get('/registered-users', [RegistrationController::class, 'getAllRegistrations']);
Route::get('/all-users', [RegistrationController::class, 'getAllRegistrations']);
Route::get('/get-users', [RegistrationController::class, 'getAllRegistrations']);

Route::get('/users/{id}', [RegistrationController::class, 'showUser']);
Route::get('/user/{id}', [RegistrationController::class, 'showUser']);
Route::get('/registrations/{id}', [RegistrationController::class, 'showUser']);
Route::get('/farmer-insurance/applications', [LoanController::class, 'getFarmerInsuranceApplications']);
Route::get('/subsidy/applications', [LoanController::class, 'getSubsidyApplications']);



Route::post('/ai/generate-image', function (Request $request) {
    $request->validate([
        'prompt' => 'required|string',
        'width' => 'integer|min:10',
        'height' => 'integer|min:10',
    ]);

    $prompt = $request->input('prompt');
    $width = $request->input('width', 1024);
    $height = $request->input('height', 1024);

    $hfToken = env('HUGGINGFACE_API_TOKEN');

    if ($hfToken) {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $hfToken,
            ])->post('https://api-inference.huggingface.co/models/stabilityai/stable-diffusion-xl-base-1.0', [
                'inputs' => $prompt,
            ]);

            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Content-Type', 'image/jpeg');
            }

            if ($response->status() == 503) {
                $body = $response->json();
                $error = $body['error'] ?? 'Hugging Face model is loading, please try again in a few seconds.';
                return response()->json(['error' => $error], 503);
            }

            $body = $response->json();
            $error = $body['error'] ?? 'Hugging Face API failed (HTTP ' . $response->status() . ')';
            return response()->json(['error' => $error], $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Hugging Face error: ' . $e->getMessage()], 500);
        }
    }

    $seed = rand(100000, 999999);
    $encodedPrompt = rawurlencode($prompt);
    $url = "https://image.pollinations.ai/prompt/{$encodedPrompt}?width={$width}&height={$height}&nologo=true&seed={$seed}";

    try {
        $response = \Illuminate\Support\Facades\Http::get($url);

        if ($response->successful()) {
            return response($response->body(), 200)
                ->header('Content-Type', 'image/jpeg');
        }

        if ($response->status() == 402) {
            return response()->json([
                'error' => "Rate limit exceeded (Queue full). This happens because you are sharing an IP address (e.g. using Cloudflare Warp or a VPN) that others are using to generate graphics. Try disabling your VPN, or configure a free HUGGINGFACE_API_TOKEN in your backend .env file to bypass this rate limit."
            ], 402);
        }

        $body = $response->json();
        $error = $body['error'] ?? 'AI service failed (HTTP ' . $response->status() . ')';
        return response()->json(['error' => $error], $response->status());
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

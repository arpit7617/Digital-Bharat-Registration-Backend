<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;
use App\Mail\RegistrationSuccessMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CashfreeWebhookController extends Controller
{
    use RegisterReferralWalletCredit;

    /**
     * Handle incoming automated webhooks from Cashfree Payment Gateway.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        Log::info("Cashfree Webhook Received: ", $request->all());

        // 1. Signature Verification (Security Check)
        $secretKey = env('CASHFREE_CLIENT_SECRET', env('CASHFREE_SECRET_KEY', env('CASHFREE_WEBHOOK_SECRET')));
        if (!empty($secretKey)) {
            $signature = $request->header('x-webhook-signature') ?? $request->header('x-cashfree-signature');
            $timestamp = $request->header('x-webhook-timestamp');
            $rawBody = $request->getContent();

            if (empty($signature)) {
                Log::warning("Cashfree Webhook: Missing signature header.");
                return response()->json(['status' => 'error', 'message' => 'Missing webhook signature header'], 401);
            }

            $signedPayload = (!empty($timestamp) ? $timestamp : '') . $rawBody;
            $expectedSignature = base64_encode(hash_hmac('sha256', $signedPayload, $secretKey, true));

            if (!hash_equals($expectedSignature, $signature)) {
                // Secondary check for legacy raw body HMAC without timestamp prefix
                $legacySignature = base64_encode(hash_hmac('sha256', $rawBody, $secretKey, true));
                if (!hash_equals($legacySignature, $signature)) {
                    Log::error("Cashfree Webhook: Invalid signature detected.");
                    return response()->json(['status' => 'error', 'message' => 'Invalid webhook signature'], 401);
                }
            }
            Log::info("Cashfree Webhook: Signature successfully verified.");
        } else {
            Log::warning("Cashfree Webhook: CASHFREE_CLIENT_SECRET is not configured in .env; skipping signature verification.");
        }

        $data = $request->all();

        $paymentStatus = null;
        $customerEmail = null;
        $customerPhone = null;
        $paymentId = null;
        $orderId = null;

        // 2. Cashfree PG v3 JSON Webhook format
        if (isset($data['data']['payment']) || isset($data['type'])) {
            $paymentData = $data['data']['payment'] ?? [];
            $orderData = $data['data']['order'] ?? [];
            $customerData = $data['data']['customer_details'] ?? [];

            $paymentStatus = $paymentData['payment_status'] ?? null;
            $paymentId = $paymentData['cf_payment_id'] ?? null;
            $orderId = $orderData['order_id'] ?? null;

            $customerEmail = $customerData['customer_email'] ?? null;
            $customerPhone = $customerData['customer_phone'] ?? null;
        } 
        // 3. Legacy / Form Data Webhook format
        else {
            $paymentStatus = $data['txStatus'] ?? $data['payment_status'] ?? $data['status'] ?? null;
            $paymentId = $data['referenceId'] ?? $data['cf_payment_id'] ?? $data['payment_id'] ?? null;
            $orderId = $data['orderId'] ?? $data['order_id'] ?? null;
            $customerEmail = $data['customerEmail'] ?? $data['email'] ?? null;
            $customerPhone = $data['customerPhone'] ?? $data['phone'] ?? $data['mobile'] ?? null;
        }

        // Check if payment was successful (SUCCESS, PAID, or OK)
        $isSuccess = in_array(strtoupper((string)$paymentStatus), ['SUCCESS', 'PAID', 'OK', 'COMPLETED']);

        if (!$isSuccess) {
            Log::info("Cashfree Webhook: Payment not successful for Order {$orderId} (Status: {$paymentStatus})");
            return response()->json(['status' => 'ignored', 'reason' => 'Payment status is not successful'], 200);
        }

        $normalizedEmail = !empty($customerEmail) ? strtolower(trim($customerEmail)) : null;

        // 4. Robust Phone Normalization (Strips +91 / 91 prefix to standard 10 digits for matching)
        $normalizedPhone = null;
        if (!empty($customerPhone)) {
            $digitsOnly = preg_replace('/\D/', '', (string)$customerPhone);
            if (strlen($digitsOnly) === 12 && str_starts_with($digitsOnly, '91')) {
                $normalizedPhone = substr($digitsOnly, 2);
            } elseif (strlen($digitsOnly) >= 10) {
                $normalizedPhone = substr($digitsOnly, -10);
            } else {
                $normalizedPhone = $digitsOnly;
            }
        }

        return DB::transaction(function () use ($normalizedEmail, $normalizedPhone, $customerPhone, $orderId, $paymentId, $customerEmail, $request) {
            // Find user by email, mobile (10-digit or exact format), or order ID with atomic row lock
            $user = null;
            if (!empty($normalizedEmail)) {
                $user = Registration::where('email', $normalizedEmail)->lockForUpdate()->first();
            }
            if (!$user && !empty($normalizedPhone)) {
                $user = Registration::where('mobile', $normalizedPhone)
                    ->orWhere('mobile', 'LIKE', '%' . $normalizedPhone)
                    ->lockForUpdate()
                    ->first();
            }
            if (!$user && !empty($orderId)) {
                $user = Registration::where('custom_id', $orderId)
                    ->orWhere('payment_id', $orderId)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$user) {
                Log::warning("Cashfree Webhook: Could not find user matching Email: {$customerEmail}, Phone: {$customerPhone} (Normalized: {$normalizedPhone}), Order: {$orderId}");
                return response()->json(['status' => 'error', 'message' => 'User record not found'], 404);
            }

            // Check if user is already marked completed to avoid duplicate email sends
            if ($user->payment_status === 'completed' && $user->payment_acknowledged) {
                Log::info("Cashfree Webhook: User ID {$user->id} already marked as completed.");
                return response()->json(['status' => 'already_processed', 'user_id' => $user->id], 200);
            }

            // Mark user as completed
            $user->update([
                'payment_status' => 'completed',
                'payment_acknowledged' => true,
                'payment_id' => $paymentId ?? $orderId ?? 'CF_' . time(),
                'transaction_id' => $orderId,
            ]);

            Log::info("Cashfree Webhook: User ID {$user->id} payment confirmed via webhook!");

            // Dispatch Welcome Confirmation Email to Portal Registrants ONLY
            // (WordPress registrants receive their email with auto-generated credentials directly from WordPress)
            if (($user->registration_source ?? '') !== 'wordpress') {
                try {
                    if (!empty($user->email)) {
                        $adminEmail = env('ADMIN_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS', 'support@digitalindiaregistration.com'));
                        Mail::to($user->email)
                            ->bcc($adminEmail)
                            ->send(new RegistrationSuccessMail($user));
                        Log::info("Cashfree Webhook: Portal registration success email dispatched to {$user->email}");
                    }
                } catch (\Exception $mailEx) {
                    Log::error("Cashfree Webhook: Failed to dispatch email for User ID {$user->id}: " . $mailEx->getMessage());
                }
            } else {
                Log::info("Cashfree Webhook: Skipped backend email for WordPress registrant User ID {$user->id} (dispatched by WordPress).");
            }

            // Credit partner referral wallet ONLY upon completed payment
            try {
                $this->creditPartnerReferralWallet($request, $user);
            } catch (\Exception $ex) {
                Log::error("Cashfree Webhook: Failed to credit partner referral wallet for User ID {$user->id}: " . $ex->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified and welcome email sent',
                'user_id' => $user->id
            ], 200);
        });
    }
}


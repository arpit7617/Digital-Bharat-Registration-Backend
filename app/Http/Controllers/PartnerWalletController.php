<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerWalletController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $userId = (int) $request->query('user_id');
        $user = Registration::find($userId);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $totalCashback = null;
        if (Schema::hasTable('partner_wallet_transactions')) {
            $totalCashback = (float) DB::table('partner_wallet_transactions')
                ->where('partner_user_id', $user->id)
                ->sum('amount');
        }

        $referralCount = 0;
        if (!empty($user->partner_code)) {
            $referralCount = Registration::query()
                ->where('referred_partner_code', $user->partner_code)
                ->where(function ($q) {
                    $q->where('payment_status', 'completed')
                      ->orWhere('payment_acknowledged', true);
                })
                ->count();
        }

        return response()->json([
            'wallet_balance' => (float) ($user->wallet_balance ?? 0),
            'partner_code' => $user->partner_code,
            'referral_count' => $referralCount,
            'total_cashback_earned' => $totalCashback,
        ]);
    }

    public function credit(Request $request): JsonResponse
    {
        $code = strtoupper(trim((string) $request->input('partner_code', '')));
        $amount = (float) $request->input('amount', $request->input('cashback_amount', 419.30));
        $refereeUserId = $request->input('referee_user_id');

        $partner = Registration::query()->where('partner_code', $code)->first();
        if (! $partner) {
            return response()->json(['message' => 'Partner not found'], 404);
        }

        DB::transaction(function () use ($partner, $amount, $code, $refereeUserId) {
            // Prevent duplicate wallet credits for the same referee user.
            if (
                $refereeUserId &&
                Schema::hasTable('partner_wallet_transactions') &&
                DB::table('partner_wallet_transactions')
                    ->where('partner_user_id', $partner->id)
                    ->where('referee_user_id', $refereeUserId)
                    ->where('source', 'registration_referral')
                    ->exists()
            ) {
                return;
            }

            $partner->wallet_balance = (float) ($partner->wallet_balance ?? 0) + $amount;
            $partner->save();

            if (Schema::hasTable('partner_wallet_transactions')) {
                DB::table('partner_wallet_transactions')->insert([
                    'partner_user_id' => $partner->id,
                    'partner_code' => $code,
                    'referee_user_id' => $refereeUserId,
                    'amount' => $amount,
                    'source' => 'registration_referral',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return response()->json([
            'wallet_balance' => $partner->wallet_balance,
            'partner_code' => $partner->partner_code,
        ]);
    }
}

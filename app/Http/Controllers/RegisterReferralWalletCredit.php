<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

trait RegisterReferralWalletCredit
{
    protected function creditPartnerReferralWallet(Request $request, ?Registration $newUser = null): ?float
    {
        $code = strtoupper(trim((string) $request->input('referred_partner_code', $request->input('referral_code', $newUser?->referred_partner_code ?? ''))));
        if ($code === '' || ! str_starts_with($code, 'PRT-')) {
            return null;
        }

        $partner = Registration::query()
            ->where('partner_code', $code)
            ->first();

        if (! $partner) {
            return null;
        }

        $amount = (float) $request->input('referral_cashback_amount', 419.30);
        if ($amount <= 0) {
            $amount = round(599 * 0.70, 2);
        }

        return DB::transaction(function () use ($partner, $amount, $code, $newUser) {
            // Prevent duplicate wallet credits for the same referee user.
            if (
                $newUser &&
                Schema::hasTable('partner_wallet_transactions') &&
                DB::table('partner_wallet_transactions')
                    ->where('partner_user_id', $partner->id)
                    ->where('referee_user_id', $newUser->id)
                    ->where('source', 'registration_referral')
                    ->exists()
            ) {
                return null;
            }

            $partner->wallet_balance = (float) ($partner->wallet_balance ?? 0) + $amount;
            $partner->save();

            // Record transaction ledger
            if (Schema::hasTable('partner_wallet_transactions')) {
                DB::table('partner_wallet_transactions')->insert([
                    'partner_user_id' => $partner->id,
                    'partner_code' => $code,
                    'referee_user_id' => $newUser?->id,
                    'amount' => $amount,
                    'source' => 'registration_referral',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $amount;
        });
    }
}

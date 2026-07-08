<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;

class ValidatePartnerCodeController extends Controller
{
    public function validateCode(Request $request)
    {
        $code = $this->normalizeCode(
            $request->input('partner_code')
            ?? $request->input('code')
            ?? $request->input('referral_code')
        );

        if ($code === '') {
            return response()->json(['valid' => false, 'message' => 'Partner code required'], 422);
        }

        return $this->lookup($code);
    }

    public function validateGet(string $code)
    {
        return $this->lookup($this->normalizeCode($code));
    }

    private function lookup(string $code)
    {
        $partner = Registration::query()
            ->where('partner_code', $code)
            ->where(function ($q) {
                $q->where('registration_type', 'partner')
                    ->orWhere('is_partner', true);
            })
            ->first();

        if (!$partner) {
            return response()->json([
                'valid' => false,
                'message' => 'Partner code not found',
            ], 200);
        }

        return response()->json([
            'valid' => true,
            'partner_name' => $partner->name,
            'partner_id' => $partner->id,
        ]);
    }

    private function normalizeCode(?string $raw): string
    {
        $s = strtoupper(trim((string) $raw));
        if ($s === '') {
            return '';
        }
        if (!str_starts_with($s, 'PRT-')) {
            $s = 'PRT-' . ltrim($s, 'PRT-');
        }
        return $s;
    }
}

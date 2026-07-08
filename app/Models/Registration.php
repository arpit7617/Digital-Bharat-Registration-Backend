<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = [
        // Basic Info
        'name',
        'mobile',
        'email',
        'password',
        'category',
        'custom_id',

        // Address Fields
        'pincode',
        'district',
        'city',
        'state',

        // Student Fields
        'college_name',
        'standard_year',
        'stream',
        'roll_number',
        'gpa',
        'graduation_year',
        'skills',

        // Business Fields
        'company_name',
        'gst_number',
        'turnover',
        'employee_count',
        'business_website',
        'establishment_year',

        // Bank Fields
        'bank_name',
        'interest_rate',
        'branch_name',
        'ifsc_code',

        // Farmer Fields
        'crop_name',
        'crop_price',
        'land_size',

        // Job Seeker Fields
        'highest_education',
        'years_of_experience',
        'preferred_job_role',

        // Partner / Wallet Fields
        'registration_type',
        'is_partner',
        'registration_fee',
        'partner_code',
        'referred_partner_code',
        'wallet_balance',
    ];

    protected static function booted()
    {
        static::creating(function ($registration) {
            // Normalize inputs
            if ($registration->registration_type === 'partner' || $registration->is_partner) {
                $registration->is_partner = true;
                $registration->registration_type = 'partner';
                
                if (empty($registration->partner_code)) {
                    $seed = $registration->mobile ?: $registration->email;
                    $registration->partner_code = static::generatePartnerCode($seed);
                }
            } else {
                $registration->is_partner = false;
                $registration->registration_type = 'normal';
                $registration->partner_code = null;
            }

            if (!empty($registration->partner_code)) {
                $registration->partner_code = strtoupper(trim($registration->partner_code));
            }
            
            if (!empty($registration->referred_partner_code)) {
                $registration->referred_partner_code = strtoupper(trim($registration->referred_partner_code));
                if (!str_starts_with($registration->referred_partner_code, 'PRT-')) {
                    $registration->referred_partner_code = 'PRT-' . ltrim($registration->referred_partner_code, 'PRT-');
                }
            }
        });
    }

    public static function generatePartnerCode($seed)
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $buffer = 'PRT-';
        if ($seed !== null && $seed !== '') {
            $hash = 0;
            $str = (string) $seed;
            for ($i = 0; $i < strlen($str); $i++) {
                $hash += ord($str[$i]);
            }
            for ($i = 0; $i < 6; $i++) {
                $charIndex = ($hash + $i * 17) % strlen($chars);
                $buffer .= $chars[$charIndex];
            }
            return $buffer;
        }
        for ($i = 0; $i < 6; $i++) {
            $buffer .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $buffer;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthInsurance extends Model
{
    protected $table = 'health_insurance_applications';

    protected $fillable = [
        'user_id', 'applicant_name', 'mobile', 'email', 'aadhaar', 'pan',
        'dob', 'gender', 'age', 'address', 'city', 'state', 'pincode',
        'plan_type', 'sum_insured', 'premium_amount', 'members_covered',
        'insurer_name', 'policy_term', 'pre_existing_disease', 'disease_details',
        'nominee_name', 'nominee_relation', 'nominee_dob',
        'status', 'claimed_by', 'details',
    ];

    protected $casts = [
        'details' => 'array',
        'pre_existing_disease' => 'boolean',
    ];
}

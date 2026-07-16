<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotorInsurance extends Model
{
    protected $table = 'motor_insurance_applications';

    protected $fillable = [
        'user_id', 'applicant_name', 'mobile', 'email', 'aadhaar', 'pan',
        'address', 'city', 'state', 'pincode',
        'vehicle_type', 'vehicle_make', 'vehicle_model', 'vehicle_year',
        'registration_number', 'engine_number', 'chassis_number', 'vehicle_value',
        'plan_type', 'insurer_name', 'premium_amount', 'policy_term',
        'has_previous_policy', 'previous_policy_number', 'previous_insurer', 'claim_history',
        'nominee_name', 'nominee_relation',
        'status', 'claimed_by', 'details',
    ];

    protected $casts = [
        'details' => 'array',
        'has_previous_policy' => 'boolean',
    ];
}

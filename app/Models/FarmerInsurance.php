<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmerInsurance extends Model
{
    protected $table = 'farmer_insurance_applications';

    protected $fillable = [
        'user_id',
        'farmer_name',
        'father_name',
        'mobile',
        'aadhaar',
        'village',
        'tehsil',
        'district',
        'state',
        'pincode',
        'land_size',
        'khasra_number',
        'survey_number',
        'crop_name',
        'season',
        'sowing_date',
        'expected_harvest',
        'sum_insured',
        'premium_amount',
        'bank_name',
        'account_number',
        'ifsc',
        'status',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];
}

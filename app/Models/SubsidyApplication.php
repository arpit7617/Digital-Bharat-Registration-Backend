<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubsidyApplication extends Model
{
    protected $table = 'subsidy_applications';

    protected $fillable = [
        'user_id',
        'applicant_name',
        'father_name',
        'mobile',
        'aadhaar',
        'village',
        'tehsil',
        'district',
        'state',
        'pincode',
        'subsidy_type',
        'scheme_name',
        'purpose',
        'land_size',
        'khasra_number',
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

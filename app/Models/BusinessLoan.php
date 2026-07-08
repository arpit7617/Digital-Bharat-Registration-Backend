<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessLoan extends Model
{
    use HasFactory;

    protected $table = 'business_loans';

    protected $fillable = [
        'user_id',
        'amount',
        'purpose',
        'tenure',
        'status',
        'details'
    ];

    protected $casts = [
        'details' => 'array',
    ];
}

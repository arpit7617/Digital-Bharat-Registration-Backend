<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerLoan extends Model
{
    use HasFactory;

    protected $table = 'farmer_loans';

    protected $fillable = [
        'user_id',
        'land_size',
        'khasra_number',
        'amount',
        'status',
        'details'
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
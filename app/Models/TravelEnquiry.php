<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelEnquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'destination',
        'travel_date',
        'passengers',
        'status',
        'claimed_by',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
        'travel_date' => 'date',
    ];
}

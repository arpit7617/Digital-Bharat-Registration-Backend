<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealEstateEnquiry extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(Registration::class, 'user_id');
    }
}

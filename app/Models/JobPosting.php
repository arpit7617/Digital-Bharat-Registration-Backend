<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    protected $table = 'job_postings';

    protected $fillable = [
        'user_id',
        'job_title',
        'description',
        'salary_range',
        'details'
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
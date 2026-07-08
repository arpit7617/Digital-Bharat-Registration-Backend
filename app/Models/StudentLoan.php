<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLoan extends Model
{
    use HasFactory;

    protected $table = 'student_loans';

    protected $fillable = [
        'user_id',
        'college_name',
        'course_name',
        'amount',
        'status',
        'details'
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
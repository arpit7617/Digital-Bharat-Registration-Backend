<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $fillable = [
        'registration_id',
        'message',
        'is_from_admin',
    ];

    protected $casts = [
        'is_from_admin' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }
}

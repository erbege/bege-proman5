<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalMatrix extends Model
{
    protected $fillable = [
        'document_type',
        'level',
        'role_name',
        'min_amount',
        'is_active',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}

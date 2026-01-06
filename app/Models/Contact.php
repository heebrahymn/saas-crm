<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company_name',
        'position',
        'source',
        'notes',
        'tags',
        'status',
    ];

    protected $casts = [
        'tags' => 'array',
        'status' => 'string',
    ];

    protected $hidden = [
        'company_id',
    ];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'assigned_to',
        'due_date',
        'priority',
        'status',
        'related_to_type',
        'related_to_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'status' => 'string',
        'priority' => 'string',
    ];

    protected $hidden = [
        'company_id',
    ];
}
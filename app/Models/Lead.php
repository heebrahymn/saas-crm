<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'contact_id',
        'title',
        'description',
        'value',
        'source',
        'status',
        'priority',
        'assigned_to',
        'estimated_close_date',
        'actual_close_date',
        'pipeline_stage',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'estimated_close_date' => 'date',
        'actual_close_date' => 'date',
        'status' => 'string',
        'priority' => 'string',
    ];

    protected $hidden = [
        'company_id',
    ];
}
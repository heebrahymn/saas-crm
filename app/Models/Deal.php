<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deal extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'contact_id',
        'lead_id',
        'title',
        'description',
        'value',
        'currency',
        'status',
        'probability',
        'estimated_close_date',
        'actual_close_date',
        'pipeline_stage',
        'assigned_to',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'probability' => 'integer',
        'estimated_close_date' => 'date',
        'actual_close_date' => 'date',
        'status' => 'string',
    ];

    protected $hidden = [
        'company_id',
    ];
}
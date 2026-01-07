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

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isWon(): bool
    {
        return $this->status === 'closed_won';
    }

    public function isLost(): bool
    {
        return $this->status === 'closed_lost';
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }
}
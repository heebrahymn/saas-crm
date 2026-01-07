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

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
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

    public function isClosed(): bool
    {
        return in_array($this->status, ['closed_won', 'closed_lost']);
    }

    public function getExpectedValue(): float
    {
        return $this->value * ($this->probability / 100);
    }
}
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
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'status' => 'string',
        'priority' => 'string',
    ];

    protected $hidden = [
        'company_id',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function relatedTo()
    {
        return $this->morphTo();
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
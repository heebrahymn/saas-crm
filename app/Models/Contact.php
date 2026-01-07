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

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'related_to');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
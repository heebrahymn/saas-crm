<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'phone',
        'job_title',
        'bio',
        'settings',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'settings' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function userRole()
    {
        return $this->hasOne(UserRole::class)->where('company_id', $this->company_id);
    }

    public function getRoleAttribute()
    {
        return $this->userRole?->role ?? 'staff';
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'invited_by');
    }

    public function isActive(): bool
    {
        return $this->is_active && !$this->trashed();
    }

    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    public function hasPermissionTo($permission): bool
    {
        // For now, we'll implement role-based permissions
        // Admin can do everything
        if ($this->hasRole('admin')) {
            return true;
        }

        // Managers and staff have limited permissions
        // This will be expanded later
        return false;
    }
}
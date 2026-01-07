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

class User extends Authenticatable
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

    public function getUserRoleAttribute()
    {
        return $this->userRole?->role ?? 'staff';
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
        // Role-based permissions
        return match($this->role) {
            'admin' => true,
            'manager' => in_array($permission, [
                'view_users', 'create_leads', 'update_leads', 
                'create_deals', 'update_deals', 'manage_tasks'
            ]),
            'staff' => in_array($permission, [
                'view_own_leads', 'update_own_leads',
                'view_own_tasks', 'update_own_tasks'
            ]),
            default => false,
        };
    }

    public function canAccessPanel($panel): bool
    {
        // Define panel access based on role
        return match($panel) {
            'admin' => $this->hasRole('admin'),
            'manager' => $this->hasRole('admin') || $this->hasRole('manager'),
            'staff' => $this->hasRole('admin') || $this->hasRole('manager') || $this->hasRole('staff'),
            default => false,
        };
    }
}
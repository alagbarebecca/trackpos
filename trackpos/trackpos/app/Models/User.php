<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['username', 'name', 'email', 'password', 'avatar', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, Auditable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles()->where('name', $role)->exists();
        }
        return $this->roles()->whereIn('id', $role->pluck('id'))->exists();
    }

    public function hasPermission($permission): bool
    {
        // Admin has all permissions
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check via roles
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }

    public function isAdmin(): bool
    {
        // Check if user has Admin role - use eager loading
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }
        
        return $this->roles->contains('name', 'Admin');
    }

    public function getRoleNamesAttribute(): string
    {
        return $this->roles->pluck('name')->implode(', ');
    }
}

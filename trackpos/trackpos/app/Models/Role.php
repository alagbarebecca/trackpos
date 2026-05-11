<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'description'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role');
    }

    public function hasPermission($permission): bool
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
        }
        
        if (!$permission) return false;
        
        return $this->permissions()->where('permission_id', $permission->id)->exists();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type', // store, warehouse, depot
        'address',
        'phone',
        'email',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'status' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(LocationProduct::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerReward extends Model
{
    protected $table = 'customer_rewards';

    protected $fillable = [
        'customer_id',
        'points',
        'total_points_earned',
        'total_points_redeemed',
        'lifetime_value',
    ];

    protected $casts = [
        'points' => 'integer',
        'total_points_earned' => 'integer',
        'total_points_redeemed' => 'integer',
        'lifetime_value' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(RewardTransaction::class);
    }
}
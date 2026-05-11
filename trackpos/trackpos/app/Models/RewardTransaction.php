<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardTransaction extends Model
{
    protected $table = 'reward_transactions';

    protected $fillable = [
        'customer_reward_id',
        'type', // earn, redeem, expire, bonus
        'points',
        'sale_id',
        'description',
    ];

    public function customerReward(): BelongsTo
    {
        return $this->belongsTo(CustomerReward::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
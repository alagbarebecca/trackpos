<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeldSale extends Model
{
    protected $fillable = [
        'reference_no', 'user_id', 'customer_id', 'subtotal', 
        'discount', 'tax', 'total', 'item_count', 'cart_data', 'hold_name'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getCartItemsAttribute()
    {
        return $this->cart_data ? json_decode($this->cart_data, true) : [];
    }
}

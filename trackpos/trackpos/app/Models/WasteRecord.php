<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteRecord extends Model
{
    protected $table = 'waste_records';

    protected $fillable = [
        'product_id',
        'user_id',
        'quantity',
        'reason', // expired, damaged, spoiled, stolen, other
        'notes',
        'value',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'value' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
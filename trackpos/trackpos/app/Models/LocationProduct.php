<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationProduct extends Model
{
    protected $table = 'location_products';

    protected $fillable = [
        'location_id',
        'product_id',
        'stock_quantity',
        'reserved_quantity',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'reserved_quantity' => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function available()
    {
        return $this->stock_quantity - $this->reserved_quantity;
    }
}
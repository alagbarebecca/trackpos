<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use Auditable;
    
    protected $fillable = [
        'name', 'sku', 'barcode', 'category_id', 'brand_id', 'unit_id',
        'cost_price', 'sell_price', 'tax_rate', 'discount_percent',
        'stock_quantity', 'reserved_stock', 'min_stock_level', 'image', 'description', 'status',
        'supplier_id'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'stock_quantity' => 'integer',
        'reserved_stock' => 'integer',
    ];

    /**
     * Calculate available stock (stock minus reserved)
     */
    public function getAvailableStockAttribute(): int
    {
        return ($this->stock_quantity ?? 0) - ($this->reserved_stock ?? 0);
    }

    /**
     * Scope to filter products with available stock
     */
    public function scopeInStock($query)
    {
        return $query->whereRaw('stock_quantity - COALESCE(reserved_stock, 0) > 0');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}

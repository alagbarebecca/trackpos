<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use Auditable;
    
    protected $table = 'stock_adjustments';
    
    protected $fillable = [
        'product_id', 'user_id', 'quantity', 'previous_quantity',
        'new_quantity', 'type', 'reason'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(Product $product, int $quantity, string $type, string $reason)
    {
        $previousQty = $product->stock_quantity;
        
        return static::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'quantity' => $quantity,
            'previous_quantity' => $previousQty,
            'new_quantity' => $product->stock_quantity,
            'type' => $type,
            'reason' => $reason,
        ]);
    }
}

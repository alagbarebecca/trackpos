<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnSale extends Model
{
    use Auditable;
    
    protected $table = 'returns';
    
    protected $fillable = [
        'return_number', 'sale_id', 'product_id', 'quantity', 
        'unit_price', 'subtotal', 'reason', 'refund_method', 
        'refund_amount', 'user_id'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate return number
     */
    public static function generateReturnNumber()
    {
        $lastReturn = self::orderBy('id', 'desc')->first();
        $number = $lastReturn ? $lastReturn->id + 1 : 1;
        return 'RET-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'purchase_no',
        'supplier_id',
        'user_id',
        'status', // pending, ordered, received, cancelled
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'notes',
        'ordered_at',
        'received_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'ordered_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public static function generatePONumber()
    {
        $last = self::orderByDesc('id')->first();
        $num = $last ? intval(substr($last->purchase_no, 3)) + 1 : 1;
        return 'PO' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
}
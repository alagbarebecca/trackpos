<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    protected $fillable = [
        'transfer_no',
        'from_location',
        'to_location',
        'user_id',
        'status',
        'notes',
        'received_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public static function generateTransferNumber()
    {
        $last = self::orderByDesc('id')->first();
        $num = $last ? intval(substr($last->transfer_no, 3)) + 1 : 1;
        return 'TRF' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
}
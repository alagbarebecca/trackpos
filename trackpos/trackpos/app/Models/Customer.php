<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use Auditable;
    
    protected $fillable = ['name', 'email', 'phone', 'address', 'city', 'loyalty_points', 'notes'];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address', 'city', 'contact_person', 'notes'];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}

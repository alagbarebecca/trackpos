<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    protected $table = 'shifts';

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'break_minutes',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getHoursWorkedAttribute()
    {
        if (!$this->clock_in) return 0;
        
        $end = $this->clock_out ?? now();
        $minutes = $this->clock_in->diffInMinutes($end) - ($this->break_minutes ?? 0);
        
        return max(0, $minutes / 60);
    }
}
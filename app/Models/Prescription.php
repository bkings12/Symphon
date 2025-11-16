<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'prescription_number',
        'prescription_date',
        'diagnosis',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'prescription_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}

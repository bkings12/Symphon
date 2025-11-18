<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankPaybillStk extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'bank_paybill_stks';

    /**
     * Get the sale that owns the Bank Paybill STK transaction
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}


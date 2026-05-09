<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'generic_name',
        'barcode',
        'sku',
        'description',
        'unit',
        'cost_price',
        'selling_price',
        'reorder_level',
        'stock_quantity',
        'requires_prescription',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    /**
     * Human-readable blockers for delete (matches FK restrict on sale_items, purchase_items, prescription_items).
     */
    public function deleteBlockerSummary(): ?string
    {
        $parts = [];

        $saleCount = $this->saleItems()->count();
        if ($saleCount > 0) {
            $parts[] = "{$saleCount} sale line item(s)";
        }

        $purchaseCount = $this->purchaseItems()->count();
        if ($purchaseCount > 0) {
            $parts[] = "{$purchaseCount} purchase line item(s)";
        }

        $rxCount = $this->prescriptionItems()->count();
        if ($rxCount > 0) {
            $parts[] = "{$rxCount} prescription line item(s)";
        }

        return $parts === [] ? null : implode(', ', $parts);
    }
}

<?php

namespace App\Concerns;

use App\Helpers\SettingsHelper;

trait ComputesCartQuantityFromMoneyAmount
{
    /**
     * Optional spend amounts keyed by medicine_id (used with "Apply" on each cart line).
     *
     * @var array<int|string, mixed>
     */
    public array $lineSpendByMedicine = [];

    public function setQuantityFromMoneyAmount(int $index): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        $medicineId = $this->cart[$index]['medicine_id'];
        $raw = $this->lineSpendByMedicine[$medicineId] ?? null;
        $amount = is_numeric($raw) ? (float) $raw : 0.0;

        if ($amount <= 0) {
            $this->dispatch('notify', message: 'Enter a spend amount greater than zero.', type: 'error');

            return;
        }

        $unit = (float) $this->cart[$index]['selling_price'];
        if ($unit <= 0) {
            $this->dispatch('notify', message: 'Unit price is zero; cannot calculate quantity.', type: 'error');

            return;
        }

        $qty = (int) floor($amount / $unit);
        if ($qty < 1) {
            $this->dispatch('notify', message: 'That amount buys less than one unit at the current unit price.', type: 'error');

            return;
        }

        $stock = (int) $this->cart[$index]['stock_available'];
        if ($qty > $stock) {
            $qty = $stock;
            $this->dispatch('notify', message: "Only {$stock} units are in stock; quantity set to the maximum.", type: 'warning');
        } else {
            $spent = $qty * $unit;
            $this->dispatch('notify', message: 'Quantity set to '.$qty.' ('.SettingsHelper::formatCurrency($spent).' at unit price).', type: 'success');
        }

        $this->cart[$index]['quantity'] = $qty;
        $this->updateCartTotals();
    }

    protected function resetLineSpendByMedicine(): void
    {
        $this->lineSpendByMedicine = [];
    }
}

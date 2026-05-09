<?php

namespace App\Support;

use App\Models\Sale;
use App\Services\ThermalPrinterService;
use Filament\Notifications\Notification;
use Throwable;

final class SaleReceiptPrinting
{
    public static function sendThermalAndNotify(Sale $sale): void
    {
        try {
            ThermalPrinterService::printSaleReceipt($sale);
            Notification::make()
                ->title('Receipt sent to printer')
                ->success()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Printing failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}

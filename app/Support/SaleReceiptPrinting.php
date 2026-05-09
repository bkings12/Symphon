<?php

namespace App\Support;

use App\Models\Sale;
use App\Services\ThermalPrinterService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Filament thermal reprint: by default calls {@see ThermalPrinterService::printSaleReceipt} in-process
 * (same bytes as the POS). Set config printing.filament_reprint_via_http to POST /thermal-print/{sale}
 * like the POS modal (requires web session).
 */
final class SaleReceiptPrinting
{
    public static function sendThermalAndNotify(Sale $sale): void
    {
        try {
            if (config('printing.filament_reprint_via_http') && ! app()->runningInConsole() && request()->hasSession()) {
                $response = Http::withHeaders([
                    'X-CSRF-TOKEN' => csrf_token(),
                    'Accept' => 'application/json',
                ])
                    ->withCookies(request()->cookies->all(), request()->getHost())
                    ->post(route('thermal.print', $sale));

                $body = $response->json() ?? [];
                if (! ($body['success'] ?? false)) {
                    throw new RuntimeException((string) ($body['message'] ?? 'Thermal print failed'));
                }
            } else {
                ThermalPrinterService::printSaleReceipt($sale);

                Log::info('Thermal print completed', [
                    'sale_id' => $sale->id,
                    'invoice' => $sale->invoice_number,
                    'path' => 'filament_in_process',
                ]);
            }

            Notification::make()
                ->title('Receipt sent to printer')
                ->success()
                ->send();
        } catch (Throwable $e) {
            Log::warning('Thermal print failed', [
                'sale_id' => $sale->id,
                'invoice' => $sale->invoice_number,
                'path' => 'filament_in_process',
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            Notification::make()
                ->title('Printing failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}

<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Support\SaleReceiptPrinting;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reprintReceipt')
                ->label('Reprint receipt')
                ->icon(Heroicon::OutlinedPrinter)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Reprint receipt')
                ->modalDescription('Send this sale to the thermal printer configured under Printing settings?')
                ->modalSubmitActionLabel('Print')
                ->action(fn () => SaleReceiptPrinting::sendThermalAndNotify($this->getRecord())),
            EditAction::make(),
        ];
    }
}

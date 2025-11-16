<?php

namespace App\Filament\Resources\StockBatches\Pages;

use App\Filament\Resources\StockBatches\StockBatchResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStockBatch extends ViewRecord
{
    protected static string $resource = StockBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

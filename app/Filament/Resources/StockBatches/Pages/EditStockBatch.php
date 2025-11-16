<?php

namespace App\Filament\Resources\StockBatches\Pages;

use App\Filament\Resources\StockBatches\StockBatchResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStockBatch extends EditRecord
{
    protected static string $resource = StockBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

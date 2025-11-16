<?php

namespace App\Filament\Resources\StockBatches\Pages;

use App\Filament\Resources\StockBatches\StockBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStockBatches extends ListRecords
{
    protected static string $resource = StockBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

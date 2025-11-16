<?php

namespace App\Filament\Resources\StockBatches;

use App\Filament\Resources\StockBatches\Pages\CreateStockBatch;
use App\Filament\Resources\StockBatches\Pages\EditStockBatch;
use App\Filament\Resources\StockBatches\Pages\ListStockBatches;
use App\Filament\Resources\StockBatches\Pages\ViewStockBatch;
use App\Filament\Resources\StockBatches\Schemas\StockBatchForm;
use App\Filament\Resources\StockBatches\Schemas\StockBatchInfolist;
use App\Filament\Resources\StockBatches\Tables\StockBatchesTable;
use App\Models\StockBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StockBatchResource extends Resource
{
    protected static ?string $model = StockBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $recordTitleAttribute = 'batch_number';

    protected static string|UnitEnum|null $navigationGroup = 'Inventory Management';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return StockBatchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StockBatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockBatches::route('/'),
            'create' => CreateStockBatch::route('/create'),
            'view' => ViewStockBatch::route('/{record}'),
            'edit' => EditStockBatch::route('/{record}/edit'),
        ];
    }
}

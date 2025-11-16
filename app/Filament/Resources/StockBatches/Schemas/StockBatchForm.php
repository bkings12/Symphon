<?php

namespace App\Filament\Resources\StockBatches\Schemas;

use App\Helpers\SettingsHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class StockBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('medicine_id')
                    ->label('Medicine')
                    ->relationship('medicine', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Select::make('purchase_id')
                    ->label('Purchase Order')
                    ->relationship('purchase', 'invoice_number')
                    ->searchable()
                    ->preload(),
                
                TextInput::make('batch_number')
                    ->maxLength(255)
                    ->label('Batch Number'),
                
                DatePicker::make('expiry_date')
                    ->label('Expiry Date')
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                
                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->label('Quantity'),
                
                TextInput::make('remaining_quantity')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->label('Remaining Quantity'),
                
                TextInput::make('cost_price')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->prefix(fn () => SettingsHelper::currencySymbol())
                    ->label('Cost Price'),
            ]);
    }
}

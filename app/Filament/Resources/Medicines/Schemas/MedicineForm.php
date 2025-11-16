<?php

namespace App\Filament\Resources\Medicines\Schemas;

use App\Helpers\SettingsHelper;
use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MedicineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                    ]),
                
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                TextInput::make('generic_name')
                    ->maxLength(255)
                    ->label('Generic Name'),
                
                TextInput::make('barcode')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('Barcode'),
                
                TextInput::make('sku')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('SKU'),
                
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                
                TextInput::make('unit')
                    ->default('pcs')
                    ->maxLength(255)
                    ->label('Unit (pcs, box, bottle, etc.)')
                    ->required(),
                
                TextInput::make('cost_price')
                    ->numeric()
                    ->default(0)
                    ->prefix(fn () => SettingsHelper::currencySymbol())
                    ->required()
                    ->label('Cost Price'),
                
                TextInput::make('selling_price')
                    ->numeric()
                    ->default(0)
                    ->prefix(fn () => SettingsHelper::currencySymbol())
                    ->required()
                    ->label('Selling Price'),
                
                TextInput::make('reorder_level')
                    ->numeric()
                    ->default(10)
                    ->required()
                    ->label('Reorder Level'),
                
                TextInput::make('stock_quantity')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->label('Current Stock'),
                
                Toggle::make('requires_prescription')
                    ->label('Requires Prescription'),
                
                Toggle::make('is_active')
                    ->default(true)
                    ->label('Active'),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Helpers\SettingsHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Select::make('pharmacy_id')
                    ->label('Pharmacy')
                    ->relationship('pharmacy', 'name')
                    ->searchable()
                    ->preload(),
                
                Select::make('user_id')
                    ->label('Purchased By')
                    ->relationship('user', 'name')
                    ->default(fn () => auth()->id())
                    ->required()
                    ->searchable()
                    ->preload(),
                
                TextInput::make('invoice_number')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('Invoice Number'),
                
                DatePicker::make('purchase_date')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->label('Purchase Date'),
                
                TextInput::make('subtotal')
                    ->numeric()
                    ->default(0)
                    ->prefix(fn () => SettingsHelper::currencySymbol())
                    ->label('Subtotal'),
                
                TextInput::make('tax_amount')
                    ->numeric()
                    ->default(0)
                    ->prefix(fn () => SettingsHelper::currencySymbol())
                    ->label('Tax Amount'),
                
                TextInput::make('discount_amount')
                    ->numeric()
                    ->default(0)
                    ->prefix(fn () => SettingsHelper::currencySymbol())
                    ->label('Discount Amount'),
                
                TextInput::make('total_amount')
                    ->numeric()
                    ->default(0)
                    ->prefix(fn () => SettingsHelper::currencySymbol())
                    ->required()
                    ->label('Total Amount'),
                
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),
                
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}

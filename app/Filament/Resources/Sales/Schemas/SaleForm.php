<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Helpers\SettingsHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pharmacy_id')
                    ->label('Pharmacy')
                    ->relationship('pharmacy', 'name')
                    ->searchable()
                    ->preload(),
                
                Select::make('user_id')
                    ->label('Sold By')
                    ->relationship('user', 'name')
                    ->default(fn () => auth()->id())
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        \Filament\Forms\Components\TextInput::make('name')->required(),
                        \Filament\Forms\Components\TextInput::make('phone')->tel(),
                    ]),
                
                Select::make('prescription_id')
                    ->label('Prescription')
                    ->relationship('prescription', 'prescription_number')
                    ->searchable()
                    ->preload(),
                
                TextInput::make('invoice_number')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->default(fn () => 'INV-' . strtoupper(uniqid()))
                    ->label('Invoice Number'),
                
                DateTimePicker::make('sale_date')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->displayFormat('d/m/Y H:i')
                    ->label('Sale Date & Time'),
                
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
                        'refunded' => 'Refunded',
                    ])
                    ->default('pending')
                    ->required(),
                
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}

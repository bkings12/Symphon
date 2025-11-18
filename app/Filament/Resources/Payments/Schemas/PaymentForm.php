<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Helpers\SettingsHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sale_id')
                    ->label('Sale')
                    ->relationship('sale', 'invoice_number')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Select::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'mpesa' => 'M-Pesa',
                        'bank_paybill' => 'Bank Paybill',
                        'card' => 'Card',
                        'insurance' => 'Insurance',
                        'other' => 'Other',
                    ])
                    ->default('cash')
                    ->required()
                    ->label('Payment Method'),
                
                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->prefix(fn () => SettingsHelper::currencySymbol())
                    ->label('Amount'),
                
                TextInput::make('reference_number')
                    ->maxLength(255)
                    ->label('Reference Number')
                    ->helperText('For M-Pesa, Card transactions, etc.'),
                
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])
                    ->default('completed')
                    ->required(),
                
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}

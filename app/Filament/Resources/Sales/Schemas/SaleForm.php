<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Helpers\SettingsHelper;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sale')
                    ->schema([
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
                            ->default(fn () => 'INV-'.strtoupper(uniqid()))
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
                    ])
                    ->columns(2),

                Section::make('Items sold')
                    ->description('Medicines included on this invoice.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->defaultItems(0)
                            ->schema([
                                Select::make('medicine_id')
                                    ->label('Medicine')
                                    ->relationship('medicine', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('stock_batch_id')
                                    ->label('Batch')
                                    ->relationship('stockBatch', 'batch_number')
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1),
                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->prefix(fn () => SettingsHelper::currencySymbol()),
                                TextInput::make('discount_amount')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix(fn () => SettingsHelper::currencySymbol()),
                            ])
                            ->columns(2)
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::syncLineTotal($data))
                            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data, Model $record): array => self::syncLineTotal($data))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function syncLineTotal(array $data): array
    {
        $qty = (float) ($data['quantity'] ?? 0);
        $unit = (float) ($data['unit_price'] ?? 0);
        $discount = (float) ($data['discount_amount'] ?? 0);
        $data['total_price'] = round(max(0, ($qty * $unit) - $discount), 2);

        return $data;
    }
}

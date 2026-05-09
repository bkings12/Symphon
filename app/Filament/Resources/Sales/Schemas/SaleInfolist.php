<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Helpers\SettingsHelper;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sale')
                    ->schema([
                        TextEntry::make('invoice_number')
                            ->label('Invoice #'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'refunded' => 'gray',
                                default => 'gray',
                            }),
                        TextEntry::make('sale_date')
                            ->label('Date & time')
                            ->dateTime(),
                        TextEntry::make('pharmacy.name')
                            ->label('Pharmacy')
                            ->placeholder('—'),
                        TextEntry::make('customer.name')
                            ->label('Customer')
                            ->placeholder('Walk-in'),
                        TextEntry::make('prescription.prescription_number')
                            ->label('Prescription')
                            ->placeholder('—'),
                        TextEntry::make('user.name')
                            ->label('Sold by'),
                        TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state)),
                        TextEntry::make('tax_amount')
                            ->label('Tax')
                            ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state)),
                        TextEntry::make('discount_amount')
                            ->label('Discount')
                            ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state)),
                        TextEntry::make('total_amount')
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state)),
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Items sold')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->table([
                                TableColumn::make('Product'),
                                TableColumn::make('Qty')->alignment(Alignment::End),
                                TableColumn::make('Unit price')->alignment(Alignment::End),
                                TableColumn::make('Discount')->alignment(Alignment::End),
                                TableColumn::make('Line total')->alignment(Alignment::End),
                            ])
                            ->schema([
                                TextEntry::make('medicine.name')
                                    ->label('Product'),
                                TextEntry::make('quantity')
                                    ->label('Qty'),
                                TextEntry::make('unit_price')
                                    ->label('Unit price')
                                    ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state)),
                                TextEntry::make('discount_amount')
                                    ->label('Discount')
                                    ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state)),
                                TextEntry::make('total_price')
                                    ->label('Line total')
                                    ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state)),
                            ])
                            ->placeholder('No line items for this sale.'),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Helpers\SettingsHelper;
use App\Models\Sale;
use App\Support\SaleReceiptPrinting;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->label('Invoice #'),
                \Filament\Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->label('Customer')
                    ->placeholder('Walk-in'),
                \Filament\Tables\Columns\TextColumn::make('sale_date')
                    ->dateTime()
                    ->sortable()
                    ->label('Date & Time'),
                \Filament\Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('# lines')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('items_sold')
                    ->label('Items sold')
                    ->getStateUsing(function (Sale $record): string {
                        $summary = $record->items
                            ->map(fn ($item) => ($item->medicine?->name ?? '?').' (×'.$item->quantity.')')
                            ->implode(', ');

                        return Str::limit($summary, 120);
                    })
                    ->wrap()
                    ->toggleable(),
                \Filament\Tables\Columns\TextColumn::make('total_amount')
                    ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state))
                    ->sortable()
                    ->label('Total')
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()
                        ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state))),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Sold By')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),
                \Filament\Tables\Filters\Filter::make('sale_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('sold_from')
                            ->label('Sold From'),
                        \Filament\Forms\Components\DatePicker::make('sold_until')
                            ->label('Sold Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['sold_from'],
                                fn ($query, $date) => $query->whereDate('sale_date', '>=', $date),
                            )
                            ->when(
                                $data['sold_until'],
                                fn ($query, $date) => $query->whereDate('sale_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('reprintReceipt')
                    ->label('Receipt')
                    ->tooltip('Reprint thermal receipt')
                    ->icon(Heroicon::OutlinedPrinter)
                    ->iconButton()
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Reprint receipt')
                    ->modalDescription('Send this sale to the thermal printer?')
                    ->modalSubmitActionLabel('Print')
                    ->action(fn (Sale $record) => SaleReceiptPrinting::sendThermalAndNotify($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sale_date', 'desc');
    }
}

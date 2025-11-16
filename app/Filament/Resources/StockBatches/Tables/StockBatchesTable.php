<?php

namespace App\Filament\Resources\StockBatches\Tables;

use App\Helpers\SettingsHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class StockBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('medicine.name')
                    ->searchable()
                    ->sortable()
                    ->label('Medicine'),
                \Filament\Tables\Columns\TextColumn::make('batch_number')
                    ->searchable()
                    ->label('Batch Number'),
                \Filament\Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->label('Expiry Date')
                    ->color(fn ($record) => $record->isExpired() ? 'danger' : ($record->isExpiringSoon() ? 'warning' : 'success')),
                \Filament\Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->label('Quantity'),
                \Filament\Tables\Columns\TextColumn::make('remaining_quantity')
                    ->numeric()
                    ->sortable()
                    ->label('Remaining')
                    ->color(fn ($record) => $record->remaining_quantity <= 0 ? 'danger' : 'success'),
                \Filament\Tables\Columns\TextColumn::make('cost_price')
                    ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state))
                    ->sortable()
                    ->label('Cost Price'),
                \Filament\Tables\Columns\TextColumn::make('purchase.invoice_number')
                    ->label('Purchase Invoice')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('medicine_id')
                    ->relationship('medicine', 'name')
                    ->label('Medicine')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\Filter::make('expired')
                    ->label('Expired')
                    ->query(fn ($query) => $query->where('expiry_date', '<', now())),
                \Filament\Tables\Filters\Filter::make('expiring_soon')
                    ->label('Expiring Soon (30 days)')
                    ->query(fn ($query) => $query->whereBetween('expiry_date', [now(), now()->addDays(30)])),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expiry_date');
    }
}

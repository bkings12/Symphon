<?php

namespace App\Filament\Resources\Purchases\Tables;

use App\Helpers\SettingsHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->label('Invoice #'),
                \Filament\Tables\Columns\TextColumn::make('supplier.name')
                    ->searchable()
                    ->sortable()
                    ->label('Supplier'),
                \Filament\Tables\Columns\TextColumn::make('purchase_date')
                    ->date()
                    ->sortable()
                    ->label('Date'),
                \Filament\Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('total_amount')
                    ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state))
                    ->sortable()
                    ->label('Total'),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Purchased By')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->label('Supplier')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                \Filament\Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('purchased_from')
                            ->label('Purchased From'),
                        \Filament\Forms\Components\DatePicker::make('purchased_until')
                            ->label('Purchased Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['purchased_from'],
                                fn ($query, $date) => $query->whereDate('purchase_date', '>=', $date),
                            )
                            ->when(
                                $data['purchased_until'],
                                fn ($query, $date) => $query->whereDate('purchase_date', '<=', $date),
                            );
                    }),
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
            ->defaultSort('purchase_date', 'desc');
    }
}

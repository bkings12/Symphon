<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Helpers\SettingsHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('sale.invoice_number')
                    ->searchable()
                    ->sortable()
                    ->label('Sale Invoice'),
                \Filament\Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'mpesa' => 'info',
                        'card' => 'warning',
                        'insurance' => 'primary',
                        default => 'gray',
                    })
                    ->sortable()
                    ->label('Method'),
                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state))
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()
                        ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state))),
                \Filament\Tables\Columns\TextColumn::make('reference_number')
                    ->searchable()
                    ->label('Reference #')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'mpesa' => 'M-Pesa',
                        'card' => 'Card',
                        'insurance' => 'Insurance',
                        'other' => 'Other',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
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
            ->defaultSort('created_at', 'desc');
    }
}

<?php

namespace App\Filament\Resources\Medicines\Tables;

use App\Helpers\SettingsHelper;
use App\Support\MedicineDeletion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class MedicinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable()
                    ->label('Category'),
                \Filament\Tables\Columns\TextColumn::make('barcode')
                    ->searchable()
                    ->label('Barcode'),
                \Filament\Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable()
                    ->label('Stock')
                    ->color(fn ($record) => $record->isLowStock() ? 'danger' : 'success'),
                \Filament\Tables\Columns\TextColumn::make('cost_price')
                    ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state))
                    ->sortable()
                    ->label('Cost'),
                \Filament\Tables\Columns\TextColumn::make('selling_price')
                    ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state))
                    ->sortable()
                    ->label('Price'),
                \Filament\Tables\Columns\IconColumn::make('requires_prescription')
                    ->boolean()
                    ->label('Rx Required')
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
                \Filament\Tables\Filters\TernaryFilter::make('requires_prescription')
                    ->label('Requires Prescription')
                    ->placeholder('All')
                    ->trueLabel('Rx Required')
                    ->falseLabel('No Rx Required'),
                \Filament\Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn ($query) => $query->whereColumn('stock_quantity', '<=', 'reorder_level')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->using(function (DeleteBulkAction $action, $records): void {
                            MedicineDeletion::bulkDeleteRecords($action, $records);
                        }),
                ]),
            ])
            ->defaultSort('name');
    }
}

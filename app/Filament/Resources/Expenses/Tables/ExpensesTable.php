<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Helpers\SettingsHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state))
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()
                        ->formatStateUsing(fn ($state) => SettingsHelper::formatCurrency($state))),
                \Filament\Tables\Columns\TextColumn::make('expense_date')
                    ->date()
                    ->sortable()
                    ->label('Date'),
                \Filament\Tables\Columns\TextColumn::make('pharmacy.name')
                    ->label('Pharmacy')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Recorded By')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\IconColumn::make('receipt_path')
                    ->boolean()
                    ->label('Receipt')
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-minus')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'rent' => 'Rent',
                        'utilities' => 'Utilities',
                        'salaries' => 'Salaries',
                        'marketing' => 'Marketing',
                        'maintenance' => 'Maintenance',
                        'supplies' => 'Supplies',
                        'insurance' => 'Insurance',
                        'other' => 'Other',
                    ]),
                \Filament\Tables\Filters\Filter::make('expense_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('expensed_from')
                            ->label('Expensed From'),
                        \Filament\Forms\Components\DatePicker::make('expensed_until')
                            ->label('Expensed Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['expensed_from'],
                                fn ($query, $date) => $query->whereDate('expense_date', '>=', $date),
                            )
                            ->when(
                                $data['expensed_until'],
                                fn ($query, $date) => $query->whereDate('expense_date', '<=', $date),
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
            ->defaultSort('expense_date', 'desc');
    }
}

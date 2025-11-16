<?php

namespace App\Filament\Resources\Prescriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class PrescriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('prescription_number')
                    ->searchable()
                    ->sortable()
                    ->label('Prescription #'),
                \Filament\Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->label('Customer'),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->label('Prescribed By')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('prescription_date')
                    ->date()
                    ->sortable()
                    ->label('Date'),
                \Filament\Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                \Filament\Tables\Filters\Filter::make('prescription_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('prescribed_from')
                            ->label('Prescribed From'),
                        \Filament\Forms\Components\DatePicker::make('prescribed_until')
                            ->label('Prescribed Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['prescribed_from'],
                                fn ($query, $date) => $query->whereDate('prescription_date', '>=', $date),
                            )
                            ->when(
                                $data['prescribed_until'],
                                fn ($query, $date) => $query->whereDate('prescription_date', '<=', $date),
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
            ->defaultSort('prescription_date', 'desc');
    }
}

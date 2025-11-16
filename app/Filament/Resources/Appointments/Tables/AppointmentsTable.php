<?php

namespace App\Filament\Resources\Appointments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appointment_number')
                    ->label('Appointment #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Patient')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.phone')
                    ->label('Phone')
                    ->searchable(),

                TextColumn::make('doctor.name')
                    ->label('Doctor')
                    ->searchable()
                    ->sortable()
                    ->default('Not Assigned'),

                TextColumn::make('queue_position')
                    ->label('Queue #')
                    ->sortable()
                    ->alignCenter(),

                SelectColumn::make('status')
                    ->options([
                        'waiting' => 'Waiting',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->selectablePlaceholder(false),

                TextColumn::make('checked_in_at')
                    ->label('Checked In')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->reason),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'waiting' => 'Waiting',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->defaultSort('queue_position', 'asc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Appointments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Appointment Information')
                    ->schema([
                        TextEntry::make('appointment_number')
                            ->label('Appointment Number'),
                        
                        TextEntry::make('queue_position')
                            ->label('Queue Position'),
                        
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'waiting' => 'warning',
                                'in_progress' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->columns(3),

                Section::make('Patient Information')
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label('Patient Name'),
                        
                        TextEntry::make('customer.phone')
                            ->label('Phone'),
                        
                        TextEntry::make('customer.email')
                            ->label('Email'),
                    ])
                    ->columns(3),

                Section::make('Staff Information')
                    ->schema([
                        TextEntry::make('doctor.name')
                            ->label('Doctor')
                            ->placeholder('Not Assigned'),
                        
                        TextEntry::make('receptionist.name')
                            ->label('Receptionist')
                            ->placeholder('N/A'),
                    ])
                    ->columns(2),

                Section::make('Visit Details')
                    ->schema([
                        TextEntry::make('reason')
                            ->label('Reason for Visit')
                            ->columnSpanFull(),
                        
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ]),

                Section::make('Timestamps')
                    ->schema([
                        TextEntry::make('checked_in_at')
                            ->label('Checked In')
                            ->dateTime(),
                        
                        TextEntry::make('started_at')
                            ->label('Started At')
                            ->dateTime()
                            ->placeholder('Not started'),
                        
                        TextEntry::make('completed_at')
                            ->label('Completed At')
                            ->dateTime()
                            ->placeholder('Not completed'),
                    ])
                    ->columns(3),
            ]);
    }
}

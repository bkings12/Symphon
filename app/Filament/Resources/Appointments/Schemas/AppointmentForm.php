<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Appointment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Patient')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        \Filament\Forms\Components\TextInput::make('name')->required(),
                        \Filament\Forms\Components\TextInput::make('phone')->tel(),
                        \Filament\Forms\Components\TextInput::make('email')->email(),
                    ])
                    ->columnSpanFull(),

                Select::make('doctor_id')
                    ->label('Doctor')
                    ->relationship('doctor', 'name')
                    ->searchable()
                    ->preload()
                    ->columnSpan(1),

                TextInput::make('appointment_number')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->default(fn () => Appointment::generateAppointmentNumber())
                    ->label('Appointment Number')
                    ->columnSpan(1)
                    ->disabled(),

                Textarea::make('reason')
                    ->rows(3)
                    ->label('Reason for Visit')
                    ->placeholder('Brief description of the patient\'s complaint or reason for visit')
                    ->columnSpanFull(),

                Select::make('status')
                    ->options([
                        'waiting' => 'Waiting',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('waiting')
                    ->required()
                    ->columnSpan(1),

                Textarea::make('notes')
                    ->rows(3)
                    ->label('Notes')
                    ->columnSpanFull(),
            ]);
    }
}

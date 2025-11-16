<?php

namespace App\Filament\Resources\Prescriptions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PrescriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        \Filament\Forms\Components\TextInput::make('name')->required(),
                        \Filament\Forms\Components\TextInput::make('phone')->tel(),
                    ]),
                
                Select::make('user_id')
                    ->label('Prescribed By')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                
                TextInput::make('prescription_number')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->default(fn () => 'PRX-' . strtoupper(uniqid()))
                    ->label('Prescription Number'),
                
                DatePicker::make('prescription_date')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->label('Prescription Date'),
                
                Textarea::make('diagnosis')
                    ->rows(3)
                    ->label('Diagnosis')
                    ->columnSpanFull(),
                
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
                
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('Phone Number'),
                
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                
                DatePicker::make('date_of_birth')
                    ->label('Date of Birth')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(now()),
                
                Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ]),
                
                Textarea::make('address')
                    ->rows(3)
                    ->columnSpanFull(),
                
                Textarea::make('medical_history')
                    ->rows(4)
                    ->label('Medical History')
                    ->columnSpanFull(),
                
                Textarea::make('allergies')
                    ->rows(3)
                    ->label('Known Allergies')
                    ->columnSpanFull(),
            ]);
    }
}

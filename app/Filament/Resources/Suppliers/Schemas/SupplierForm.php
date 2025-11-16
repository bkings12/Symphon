<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                TextInput::make('code')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('Supplier Code'),
                
                TextInput::make('contact_person')
                    ->maxLength(255)
                    ->label('Contact Person'),
                
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                
                Textarea::make('address')
                    ->rows(3)
                    ->columnSpanFull(),
                
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
                
                Toggle::make('is_active')
                    ->default(true)
                    ->label('Active'),
            ]);
    }
}

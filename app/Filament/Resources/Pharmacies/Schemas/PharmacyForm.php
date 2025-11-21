<?php

namespace App\Filament\Resources\Pharmacies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PharmacyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Enter the pharmacy basic details')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        TextInput::make('name')
                            ->label('Pharmacy Name')
                            ->placeholder('e.g., Main Branch Pharmacy')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        
                        TextInput::make('code')
                            ->label('Pharmacy Code')
                            ->placeholder('e.g., PH-001')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText('Unique identifier for this pharmacy')
                            ->columnSpan(1),
                        
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Is this pharmacy currently operational?')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                
                Section::make('Contact Information')
                    ->description('Enter contact details for this pharmacy')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->placeholder('+254 712 345 678')
                            ->tel()
                            ->maxLength(20)
                            ->columnSpan(1),
                        
                        TextInput::make('email')
                            ->label('Email Address')
                            ->placeholder('pharmacy@example.com')
                            ->email()
                            ->maxLength(255)
                            ->columnSpan(1),
                        
                        Textarea::make('address')
                            ->label('Physical Address')
                            ->placeholder('Enter the full address including street, city, and country')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Section::make('Licensing Information')
                    ->description('Enter pharmacy licensing and registration details')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextInput::make('license_number')
                            ->label('License Number')
                            ->placeholder('e.g., PH-LIC-2024-001')
                            ->maxLength(100)
                            ->helperText('Official pharmacy license number')
                            ->columnSpan(1),
                        
                        TextInput::make('tax_id')
                            ->label('Tax ID / Registration Number')
                            ->placeholder('e.g., P051234567A')
                            ->maxLength(100)
                            ->helperText('Business tax identification or registration number')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }
}
